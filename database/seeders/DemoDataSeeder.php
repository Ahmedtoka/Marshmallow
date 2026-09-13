<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Lead;
use App\Models\User;
use App\Support\ClassFinder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 60 days of realistic website traffic plus the website leads it produced, so the analytics and CRM screens
 * can be previewed. Local / staging only — refuses to run in production. Re-running replaces the previous demo data.
 *
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Demo rows are marked: visits.ip_hash = 'demo', leads.utm_content = 'demo'. Remove all demo data with:
 *
 *   php artisan tinker --execute="App\Models\Lead::withTrashed()->where('utm_content','demo')->forceDelete(); DB::table('visits')->where('ip_hash','demo')->delete(); App\Models\Visitor::whereNull('lead_id')->doesntHave('visits')->delete();"
 *
 * (page views, events, lead activities and follow-ups are removed by their foreign-key cascades.)
 */
class DemoDataSeeder extends Seeder
{
    private const DAYS = 60;

    private CarbonImmutable $now;

    private Collection $classes;

    private Collection $branches;

    private array $salesByBranch = [];

    private string $academicYear;

    private array $sections = [];

    private array $faqs = [];

    private array $albums = [];

    private array $activities = [];

    private array $camps = [];

    /** uuid => visitor row */
    private array $visitors = [];

    /** uuid => [finder_months, finder_label, branch, has_lead] */
    private array $visitorMeta = [];

    /** uuids of visitors who can come back */
    private array $pool = [];

    private array $visits = [];

    private array $pageViews = [];

    private array $events = [];

    private array $conversions = [];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoDataSeeder refuses to run in production.');

            return;
        }

        $started = microtime(true);
        mt_srand(20260914);
        $this->now = CarbonImmutable::now();

        $this->wipe();
        $this->loadReference();

        if ($this->classes->isEmpty() || $this->branches->isEmpty()) {
            $this->command?->error('Seed branches and classes first: php artisan db:seed');

            return;
        }

        $this->generateTraffic();
        [$visitorIds, $visitIds] = $this->insertTraffic();
        $leads = $this->createLeads($visitorIds, $visitIds);

        $this->command?->info(sprintf(
            'Demo data: %d visitors, %d visits, %d page views, %d events, %d leads in %.1fs.',
            count($this->visitors), count($this->visits), count($this->pageViews), count($this->events), $leads, microtime(true) - $started
        ));
    }

    /* ------------------------------------------------------------------ */

    private function wipe(): void
    {
        Lead::withTrashed()->where('utm_content', 'demo')->forceDelete();
        DB::table('visits')->where('ip_hash', 'demo')->delete();
        DB::table('visitors')->whereNull('lead_id')->whereNotExists(fn ($q) => $q->from('visits')->whereColumn('visits.visitor_id', 'visitors.id'))->delete();
    }

    private function loadReference(): void
    {
        $this->classes = Classroom::orderBy('min_months')->get(['id', 'name', 'slug', 'min_months', 'max_months']);
        $this->branches = Branch::orderBy('sort_order')->get(['id', 'name']);
        foreach (User::where('role', 'sales')->where('is_active', true)->get(['id', 'branch_id']) as $user) {
            $this->salesByBranch[$user->branch_id] ??= $user->id;
        }
        $this->academicYear = ClassFinder::academicYears()[0];

        $this->sections = DB::table('sections')->orderBy('sort_order')->pluck('key')->all()
            ?: ['hero', 'class_finder', 'why', 'classes', 'activities', 'safety', 'camps', 'testimonials', 'partners', 'gallery', 'faq', 'branches', 'enroll_cta'];
        $this->faqs = DB::table('faqs')->pluck('question')->all() ?: ['What ages do you accept?', 'What are your opening hours?', 'Do you provide transport?'];
        $this->albums = DB::table('gallery_albums')->pluck('title', 'slug')->all();
        $this->activities = DB::table('activities')->pluck('name', 'slug')->all();
        $this->camps = DB::table('camps')->pluck('id', 'slug')->all();
    }

    /* ------------------------------------------------------------------
     | Traffic generation (in memory)
     * ------------------------------------------------------------------ */

    private function generateTraffic(): void
    {
        $today = $this->now->startOfDay();
        $hourWeights = [0 => 1, 1 => 1, 2 => 0, 3 => 0, 4 => 0, 5 => 1, 6 => 1, 7 => 2, 8 => 3, 9 => 4, 10 => 5, 11 => 5, 12 => 5, 13 => 4,
            14 => 4, 15 => 4, 16 => 4, 17 => 4, 18 => 5, 19 => 6, 20 => 7, 21 => 8, 22 => 7, 23 => 4];

        for ($d = self::DAYS - 1; $d >= 0; $d--) {
            $day = $today->subDays($d);
            $count = mt_rand(70, 120);
            if (in_array($day->dayOfWeek, [5, 6], true)) {        // Friday & Saturday are quieter
                $count = (int) round($count * 0.58);
            }
            $count = (int) round($count * (0.88 + 0.12 * (self::DAYS - $d) / self::DAYS)); // gentle growth
            $count = max(40, min(120, $count));

            $starts = [];
            for ($i = 0; $i < $count; $i++) {
                $starts[] = $day->setTime((int) $this->pick($hourWeights), mt_rand(0, 59), mt_rand(0, 59));
            }
            if ($d === 0) {
                $starts = array_values(array_filter($starts, fn ($s) => $s->lessThan($this->now->subMinutes(10))));
            }
            usort($starts, fn ($a, $b) => $a <=> $b);

            foreach ($starts as $start) {
                $this->makeVisit($start);
            }
        }

        // A few people browsing right now.
        foreach (range(1, 3) as $i) {
            $this->makeVisit(null, true);
        }
    }

    private function makeVisit(?CarbonImmutable $start, bool $activeNow = false): void
    {
        $returning = count($this->pool) > 40 && mt_rand(1, 100) <= 20;
        $visitorUuid = $returning ? $this->pool[array_rand($this->pool)] : (string) Str::uuid();

        if ($returning) {
            $source = $this->pick(['direct' => 40, 'whatsapp' => 18, 'facebook' => 24, 'google' => 12, 'instagram' => 6]);
            $device = $this->visitors[$visitorUuid]['device_type'];
            $os = $this->visitors[$visitorUuid]['os'];
        } else {
            $source = $this->pick(['facebook' => 45, 'direct' => 15, 'google' => 15, 'instagram' => 10, 'whatsapp' => 7, 'meta_ads' => 5, 'referral' => 3]);
            $device = $this->pick(['mobile' => 72, 'desktop' => 20, 'tablet' => 8]);
            $os = $device === 'desktop' ? $this->pick(['Windows' => 80, 'macOS' => 20]) : $this->pick(['Android' => 64, 'iOS' => 36]);
        }

        $browser = match (true) {
            $device !== 'desktop' && in_array($source, ['facebook', 'meta_ads'], true) && mt_rand(1, 100) <= 70 => 'Facebook app',
            $device !== 'desktop' && $source === 'instagram' && mt_rand(1, 100) <= 75 => 'Instagram app',
            $os === 'iOS' || $os === 'macOS' => $this->pick(['Safari' => 80, 'Chrome' => 20]),
            $os === 'Android' => $this->pick(['Chrome' => 80, 'Samsung Internet' => 20]),
            default => $this->pick(['Chrome' => 75, 'Edge' => 20, 'Firefox' => 5]),
        };

        [$referrer, $utm, $clickId] = $this->attribution($source);
        $landing = $this->landingPath($source, $utm['utm_campaign'] ?? null);

        // ---- Pages ----
        $bounce = mt_rand(1, 100) <= 42;
        $target = $bounce ? 1 : (int) $this->pick([2 => 30, 3 => 25, 4 => 18, 5 => 12, 6 => 8, 7 => 5, 8 => 2]);
        $meta = $this->visitorMeta[$visitorUuid] ?? ['finder_months' => null, 'finder_label' => null, 'branch' => null, 'has_lead' => false];

        $pages = [];
        $events = [];
        $offset = 0;
        $path = $landing;
        $converted = false;
        $formStarted = false;
        $submitAt = null;

        for ($p = 0; $p < $target; $p++) {
            $single = $target === 1;
            $duration = $this->pageDuration($path, $single);
            $scroll = $this->scrollDepth($path, $single);
            $pageEvents = [];

            $at = fn (float $fraction) => $offset + max(1, (int) round($duration * $fraction));

            switch (true) {
                case $path === '/':
                    $seen = max(1, (int) ceil($scroll / 100 * count($this->sections)));
                    foreach (array_slice($this->sections, 0, $seen) as $i => $key) {
                        $pageEvents[] = ['section_view', $key, null, $at(($i + 0.5) / $seen * 0.9)];
                    }
                    if (! $single || mt_rand(1, 100) <= 25) {
                        if ($seen >= 2 && mt_rand(1, 100) <= 24) {
                            $pageEvents[] = $this->classFinderEvent($meta, $at(0.3));
                        }
                        if (mt_rand(1, 100) <= 14) {
                            $pageEvents[] = ['cta_click', $this->pick(['Hero – Book a visit' => 40, 'Hero – Find my class' => 25, 'Classes – See all classes' => 15, 'Enroll banner – Book a tour' => 20]), null, $at(0.95)];
                        }
                        if ($seen >= 11 && mt_rand(1, 100) <= 30) {
                            foreach ((array) array_rand(array_flip($this->faqs), min(count($this->faqs), mt_rand(1, 2))) as $j => $q) {
                                $pageEvents[] = ['faq_open', $q, null, $at(0.8 + $j * 0.05)];
                            }
                        }
                        if (mt_rand(1, 100) <= 2) {
                            $pageEvents[] = ['outbound_click', 'www.facebook.com', ['url' => 'https://www.facebook.com/marshmallownursery'], $at(0.98)];
                        }
                    }
                    break;
                case $path === '/classes':
                    if (mt_rand(1, 100) <= 30) {
                        $pageEvents[] = $this->classFinderEvent($meta, $at(0.4));
                    }
                    break;
                case str_starts_with($path, '/classes/'):
                    if (mt_rand(1, 100) <= 20) {
                        $name = $this->classes->firstWhere('slug', substr($path, 9))?->name ?? 'class';
                        $pageEvents[] = ['cta_click', 'Class page – Enroll in '.$name, null, $at(0.9)];
                    }
                    break;
                case $path === '/branches':
                    foreach (['call_click' => 18, 'whatsapp_click' => 22, 'map_click' => 20] as $name => $chance) {
                        if (mt_rand(1, 100) <= $chance) {
                            $pageEvents[] = $this->contactEvent($name, $meta, $at(mt_rand(40, 95) / 100));
                        }
                    }
                    break;
                case str_starts_with($path, '/gallery/') && mt_rand(1, 100) <= 60:
                    $title = $this->albums[substr($path, 9)] ?? 'Album';
                    foreach (range(1, mt_rand(1, 4)) as $j) {
                        $pageEvents[] = ['gallery_open', $title, null, $at(0.15 * $j)];
                    }
                    break;
                case str_starts_with($path, '/activities/') && mt_rand(1, 100) <= 8:
                    $pageEvents[] = ['video_play', $this->activities[substr($path, 12)] ?? 'Activity video', null, $at(0.3)];
                    break;
                case str_starts_with($path, '/camps/') && mt_rand(1, 100) <= 25:
                    $pageEvents[] = ['cta_click', 'Camp – Book a place', null, $at(0.85)];
                    break;
                case $path === '/enroll':
                    if (! $formStarted && mt_rand(1, 100) <= 35) {
                        $formStarted = true;
                        $duration = max($duration, mt_rand(90, 280));
                        $pageEvents[] = ['form_start', 'enroll', null, $offset + mt_rand(4, 20)];
                        if (! $meta['has_lead'] && ! $activeNow && mt_rand(1, 100) <= 45) {
                            $converted = true;
                            $meta['has_lead'] = true;
                            $submitAt = $offset + $duration - mt_rand(2, 8);
                            $pageEvents[] = ['form_submit', 'enroll', null, $submitAt];
                        }
                    }
                    break;
            }

            // Floating WhatsApp button and header phone number on every page.
            if ($path !== '/branches' && ! $single) {
                if (mt_rand(1, 100) <= 4) {
                    $pageEvents[] = $this->contactEvent('whatsapp_click', $meta, $at(0.6));
                }
                if (mt_rand(1, 100) <= 2) {
                    $pageEvents[] = $this->contactEvent('call_click', $meta, $at(0.7));
                }
            }

            $pages[] = [$path, $offset, $duration, $scroll];
            foreach ($pageEvents as $e) {
                $events[] = [$e[0], $e[1], $e[2], min($e[3], $offset + $duration), $path];
            }

            $offset += $duration + mt_rand(1, 4);

            if ($converted && $path === '/enroll') {
                $path = '/thank-you';
                if ($p === $target - 1) {
                    $target++;
                }
                continue;
            }
            if ($path === '/thank-you' && mt_rand(1, 100) <= 70) {
                break;
            }
            $path = $this->nextPath($path);
        }

        $last = end($pages);
        $wall = $last[1] + $last[2];

        if ($activeNow) {
            $start = $this->now->subSeconds($wall + mt_rand(10, 170));
        } elseif ($start->addSeconds($wall)->greaterThan($this->now->subMinutes(6))) {
            $start = $this->now->subSeconds($wall + mt_rand(400, 2400));
        }

        $engagedEvents = array_filter($events, fn ($e) => ! in_array($e[0], ['section_view', 'outbound_click'], true));
        $visitUuid = (string) Str::uuid();
        $ts = fn (int $sec) => $start->addSeconds($sec)->toDateTimeString();

        $this->visits[] = [
            'uuid' => $visitUuid,
            '_visitor' => $visitorUuid,
            'started_at' => $start->toDateTimeString(),
            'last_activity_at' => $ts($wall),
            'duration_seconds' => $wall,
            'pageviews' => count($pages),
            'events_count' => count($events),
            'landing_path' => $landing,
            'exit_path' => $last[0],
            'referrer' => $referrer,
            'referrer_host' => $referrer ? parse_url($referrer, PHP_URL_HOST) : null,
            'source' => $source,
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content' => null,
            'utm_term' => null,
            'click_id' => $clickId,
            'device_type' => $device,
            'browser' => $browser,
            'os' => $os,
            'screen' => $device === 'desktop' ? $this->pick(['1920x1080' => 60, '1366x768' => 25, '1536x864' => 15]) : ($device === 'tablet' ? '810x1080' : $this->pick(['412x915' => 45, '390x844' => 35, '360x800' => 20])),
            'language' => $this->pick(['en-US' => 45, 'ar-EG' => 40, 'en-GB' => 15]),
            'country' => 'EG',
            'ip_hash' => 'demo',
            'is_bounce' => count($pages) === 1 && ! $engagedEvents,
            'converted' => $converted,
            'created_at' => $start->toDateTimeString(),
            'updated_at' => $ts($wall),
        ];

        foreach ($pages as [$pPath, $pOffset, $pDuration, $pScroll]) {
            $this->pageViews[] = [
                '_visit' => $visitUuid, '_visitor' => $visitorUuid,
                'path' => $pPath,
                'title' => null,
                'query' => null,
                'entered_at' => $ts($pOffset),
                'duration_seconds' => $pDuration,
                'max_scroll' => $pScroll,
                'created_at' => $ts($pOffset),
                'updated_at' => $ts($pOffset + $pDuration),
            ];
        }
        foreach ($events as [$name, $label, $props, $eOffset, $ePath]) {
            $this->events[] = [
                '_visit' => $visitUuid, '_visitor' => $visitorUuid,
                'page_view_id' => null,
                'name' => $name,
                'label' => $label,
                'value' => null,
                'properties' => $props ? json_encode($props) : null,
                'path' => $ePath,
                'created_at' => $ts($eOffset),
            ];
        }

        // ---- Visitor ----
        if (! isset($this->visitors[$visitorUuid])) {
            $this->visitors[$visitorUuid] = [
                'uuid' => $visitorUuid,
                'first_seen_at' => $start->toDateTimeString(),
                'last_seen_at' => $ts($wall),
                'visits_count' => 0,
                'pageviews_count' => 0,
                'first_source' => $source,
                'first_referrer_host' => $referrer ? parse_url($referrer, PHP_URL_HOST) : null,
                'first_utm_source' => $utm['utm_source'] ?? null,
                'first_utm_campaign' => $utm['utm_campaign'] ?? null,
                'first_landing_path' => $landing,
                'device_type' => $device,
                'browser' => $browser,
                'os' => $os,
                'country' => 'EG',
                'lead_id' => null,
                'created_at' => $start->toDateTimeString(),
                'updated_at' => $ts($wall),
            ];
            $this->pool[] = $visitorUuid;
        }
        $this->visitors[$visitorUuid]['visits_count']++;
        $this->visitors[$visitorUuid]['pageviews_count'] += count($pages);
        $this->visitors[$visitorUuid]['last_seen_at'] = max($this->visitors[$visitorUuid]['last_seen_at'], $ts($wall));
        $this->visitors[$visitorUuid]['updated_at'] = $this->visitors[$visitorUuid]['last_seen_at'];
        $this->visitorMeta[$visitorUuid] = $meta;

        if ($converted) {
            $this->conversions[] = ['visit' => $visitUuid, 'visitor' => $visitorUuid, 'at' => $start->addSeconds($submitAt + 1)];
        }
    }

    private function attribution(string $source): array
    {
        $campaign = fn (array $w) => $this->pick($w);

        return match ($source) {
            'facebook' => [$this->pick(['https://m.facebook.com/' => 60, 'https://l.facebook.com/' => 40]),
                mt_rand(1, 100) <= 25 ? ['utm_source' => 'facebook', 'utm_medium' => 'social', 'utm_campaign' => $campaign(['admissions_2026' => 55, 'summer_camp' => 45])] : [], null],
            'instagram' => ['https://l.instagram.com/',
                mt_rand(1, 100) <= 20 ? ['utm_source' => 'instagram', 'utm_medium' => 'social', 'utm_campaign' => 'admissions_2026'] : [], null],
            'google' => ['https://www.google.com/', [], null],
            'whatsapp' => [null, ['utm_source' => 'whatsapp', 'utm_medium' => 'broadcast'] + (mt_rand(1, 100) <= 50 ? ['utm_campaign' => 'admissions_2026'] : []), null],
            'meta_ads' => ['https://m.facebook.com/', ['utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => $campaign(['admissions_2026' => 70, 'summer_camp' => 30])], 'fbclid'],
            'referral' => ['https://'.$this->pick(['www.cairo-moms.com' => 40, 'nurseryfinder-eg.com' => 35, 'giza-parents.blogspot.com' => 25]).'/', [], null],
            default => [null, [], null],
        };
    }

    private function landingPath(string $source, ?string $campaign): string
    {
        if ($campaign === 'summer_camp') {
            return mt_rand(1, 100) <= 70 && isset($this->camps['summer-camp-2027']) ? '/camps/summer-camp-2027' : '/camps';
        }
        if ($campaign === 'admissions_2026') {
            return $this->resolvePath($this->pick(['/enroll' => 15, '/' => 60, '/classes' => 25]));
        }
        if ($source === 'google') {
            return $this->resolvePath($this->pick(['/' => 55, '/classes' => 12, '/branches' => 12, 'class' => 8, '/about' => 5, '/safety' => 4, '/careers' => 4]));
        }

        return $this->resolvePath($this->pick(['/' => 60, 'class' => 10, '/classes' => 8, '/gallery' => 6, '/enroll' => 6, '/branches' => 6, '/activities' => 4]));
    }

    private function nextPath(string $path): string
    {
        $map = [
            '/' => ['/classes' => 20, 'class' => 18, '/enroll' => 6, '/branches' => 10, '/gallery' => 8, '/activities' => 8, '/about' => 6, '/safety' => 6, '/camps' => 5],
            '/classes' => ['class' => 55, '/enroll' => 10, '/' => 10, '/branches' => 8, '/activities' => 7],
            'class' => ['/enroll' => 15, 'class' => 22, '/classes' => 17, '/branches' => 13, 'activity' => 9, '/gallery' => 6, '/' => 12],
            '/activities' => ['activity' => 60, '/classes' => 15, '/' => 10, '/enroll' => 7],
            'activity' => ['activity' => 30, '/activities' => 20, 'class' => 20, '/enroll' => 7, '/' => 15],
            '/camps' => ['camp' => 60, '/enroll' => 12, '/' => 15],
            'camp' => ['/enroll' => 25, '/camps' => 20, '/branches' => 15, '/' => 20],
            '/gallery' => ['album' => 70, '/' => 15, '/enroll' => 7],
            'album' => ['album' => 30, '/gallery' => 30, '/enroll' => 10, '/' => 20],
            '/branches' => ['/enroll' => 20, '/' => 30, '/classes' => 15, '/about' => 15],
            '/about' => ['/classes' => 30, '/safety' => 20, '/branches' => 25, '/enroll' => 12],
            '/safety' => ['/classes' => 30, '/branches' => 25, '/enroll' => 15, '/' => 15],
            '/careers' => ['/' => 60, '/about' => 40],
            '/enroll' => ['/branches' => 30, '/classes' => 25, '/' => 25, 'class' => 20],
            '/thank-you' => ['/' => 60, '/gallery' => 40],
        ];

        $kind = match (true) {
            str_starts_with($path, '/classes/') => 'class',
            str_starts_with($path, '/activities/') => 'activity',
            str_starts_with($path, '/camps/') => 'camp',
            str_starts_with($path, '/gallery/') => 'album',
            default => $path,
        };

        for ($try = 0; $try < 3; $try++) {
            $next = $this->resolvePath($this->pick($map[$kind] ?? $map['/']));
            if ($next !== $path) {
                return $next;
            }
        }

        return '/';
    }

    private function resolvePath(string $choice): string
    {
        return match ($choice) {
            'class' => '/classes/'.$this->classes->random()->slug,
            'activity' => $this->activities ? '/activities/'.array_rand($this->activities) : '/activities',
            'camp' => $this->camps ? '/camps/'.array_rand($this->camps) : '/camps',
            'album' => $this->albums ? '/gallery/'.array_rand($this->albums) : '/gallery',
            default => $choice,
        };
    }

    private function pageDuration(string $path, bool $single): int
    {
        [$min, $max] = match (true) {
            $single => [3, 70],
            $path === '/' => [20, 160],
            str_starts_with($path, '/classes/') => [30, 210],
            $path === '/enroll' => [30, 200],
            $path === '/thank-you' => [5, 30],
            str_starts_with($path, '/gallery/') => [20, 130],
            default => [15, 120],
        };

        return $min + (int) round(($max - $min) * (mt_rand() / mt_getrandmax()) ** 1.6);
    }

    private function scrollDepth(string $path, bool $single): int
    {
        [$min, $max] = match (true) {
            $path === '/thank-you' => [80, 100],
            $single => [8, 70],
            $path === '/enroll' => [40, 100],
            default => [25, 100],
        };

        return mt_rand($min, $max);
    }

    private function classFinderEvent(array &$meta, int $offset): array
    {
        $label = $this->pick(['Cupcake' => 18, 'Popcorn' => 16, 'Candy' => 17, 'Ice Cream' => 16, 'Lollipop' => 14, 'Cotton Candy' => 12, 'Too young' => 5, 'Too old' => 2]);
        $class = $this->classes->firstWhere('name', $label);
        $months = match (true) {
            $label === 'Too young' => mt_rand(3, 8),
            $label === 'Too old' => mt_rand(72, 84),
            $class !== null => mt_rand($class->min_months, ($class->max_months ?? 72) - 1),
            default => mt_rand(12, 60),
        };
        $meta['finder_months'] = $months;
        $meta['finder_label'] = $label;

        return ['class_finder', $label, ['months' => $months, 'year' => $this->academicYear], $offset];
    }

    private function contactEvent(string $name, array &$meta, int $offset): array
    {
        $branch = $meta['branch'] ? $this->branches->firstWhere('id', $meta['branch']) : null;
        $branch ??= mt_rand(1, 100) <= 55 ? $this->branches->first() : $this->branches->last();
        $meta['branch'] = $branch->id;

        $verb = ['call_click' => 'Call', 'whatsapp_click' => 'WhatsApp', 'map_click' => 'Directions to'][$name];

        return [$name, $verb.' '.$branch->name, null, $offset];
    }

    /** @param  array<string|int, int>  $weights */
    private function pick(array $weights): string|int
    {
        $total = array_sum($weights);
        $roll = mt_rand(1, max(1, $total));
        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    /* ------------------------------------------------------------------
     | Bulk insert
     * ------------------------------------------------------------------ */

    private function insertTraffic(): array
    {
        $visitorIds = [];
        foreach (array_chunk($this->visitors, 500) as $chunk) {
            DB::table('visitors')->insert($chunk);
            $visitorIds += DB::table('visitors')->whereIn('uuid', array_column($chunk, 'uuid'))->pluck('id', 'uuid')->all();
        }

        $visitIds = [];
        foreach (array_chunk($this->visits, 500) as $chunk) {
            $rows = array_map(function ($row) use ($visitorIds) {
                $row['visitor_id'] = $visitorIds[$row['_visitor']];
                unset($row['_visitor']);

                return $row;
            }, $chunk);
            DB::table('visits')->insert($rows);
            $visitIds += DB::table('visits')->whereIn('uuid', array_column($rows, 'uuid'))->pluck('id', 'uuid')->all();
        }

        $link = function (array $rows) use ($visitorIds, $visitIds) {
            return array_map(function ($row) use ($visitorIds, $visitIds) {
                $row['visit_id'] = $visitIds[$row['_visit']];
                $row['visitor_id'] = $visitorIds[$row['_visitor']];
                unset($row['_visit'], $row['_visitor']);

                return $row;
            }, $rows);
        };

        foreach (array_chunk($this->pageViews, 1000) as $chunk) {
            DB::table('page_views')->insert($link($chunk));
        }
        foreach (array_chunk($this->events, 1000) as $chunk) {
            DB::table('tracking_events')->insert($link($chunk));
        }

        return [$visitorIds, $visitIds];
    }

    /* ------------------------------------------------------------------
     | Leads (Lead::create, not LeadService, so no notification flood)
     * ------------------------------------------------------------------ */

    private function createLeads(array $visitorIds, array $visitIds): int
    {
        $visitsByUuid = array_column($this->visits, null, 'uuid');
        $reference = ClassFinder::referenceDate($this->academicYear);
        $mothers = ['Dina', 'Mariam', 'Nourhan', 'Rana', 'Heba', 'Yasmin', 'Sara', 'Aya', 'Menna', 'Reem', 'Shaimaa', 'Mai', 'Nada', 'Esraa', 'Hend', 'Salma', 'Radwa', 'Noha'];
        $fathers = ['Mohamed', 'Ahmed', 'Omar', 'Karim', 'Mostafa', 'Tarek', 'Amr', 'Hassan', 'Sherif', 'Mahmoud', 'Khaled', 'Islam'];
        $lastNames = ['Ahmed', 'Samir', 'Hassan', 'El Sayed', 'Mostafa', 'Farouk', 'Adel', 'Fathy', 'Kamal', 'Nabil', 'Abdelrahman', 'Saleh', 'Gamal', 'Mansour', 'Ezzat', 'Ashraf', 'Hamdy', 'Lotfy'];
        $children = ['Mariam', 'Omar', 'Nour', 'Youssef', 'Salma', 'Hamza', 'Malak', 'Ali', 'Jana', 'Adam', 'Farida', 'Yassin', 'Layla', 'Zeyad', 'Talia', 'Seif', 'Hana', 'Eyad', 'Lara', 'Taim', 'Retaj', 'Mazen'];
        $messages = [
            'Is there a place available in September?', 'What are the fees and do you have transport?', 'We would like to visit the branch this week.',
            'My daughter is not potty trained yet, is that ok?', 'Do you offer a sibling discount?', 'Can we do a trial day first?', null, null, null,
        ];
        $callNotes = [
            'Called, parent asked about fees and transport. Sent the price list on WhatsApp.', 'No answer, sent a WhatsApp message.',
            'Spoke to the mother, interested in a tour next week.', 'Parent comparing with another nursery nearby, will decide soon.',
            'Explained the daily routine and the English curriculum. Very interested.',
        ];
        $outcomes = ['Parent confirmed the tour', 'Sent fees on WhatsApp', 'No answer, will try again', 'Parent will come with the father', 'Discussed start date'];

        $activities = [];
        $followUps = [];
        $count = 0;

        foreach ($this->conversions as $conversion) {
            $visit = $visitsByUuid[$conversion['visit']];
            $meta = $this->visitorMeta[$conversion['visitor']];
            /** @var CarbonImmutable $createdAt */
            $createdAt = $conversion['at'];
            $ageDays = (int) $createdAt->diffInDays($this->now);

            $branchId = $meta['branch'] ?? (mt_rand(1, 100) <= 55 ? $this->branches->first()->id : $this->branches->last()->id);
            $agent = $this->salesByBranch[$branchId] ?? null;

            $months = $meta['finder_months'] ?? (function () {
                $class = $this->classes->random();

                return mt_rand($class->min_months, ($class->max_months ?? 72) - 1);
            })();
            $classroom = $this->classes->first(fn ($c) => $months >= $c->min_months && ($c->max_months === null || $months < $c->max_months));
            $dob = $reference->subMonths($months)->subDays(mt_rand(1, 27));

            $status = $this->pick(match (true) {
                $ageDays < 3 => ['new' => 70, 'contacted' => 25, 'tour_booked' => 5],
                $ageDays < 7 => ['new' => 35, 'contacted' => 40, 'tour_booked' => 20, 'lost' => 5],
                $ageDays < 21 => ['new' => 5, 'contacted' => 25, 'tour_booked' => 22, 'toured' => 22, 'enrolled' => 12, 'lost' => 14],
                default => ['new' => 3, 'contacted' => 12, 'tour_booked' => 8, 'toured' => 17, 'enrolled' => 34, 'lost' => 26],
            });

            $interest = match (true) {
                $meta['finder_label'] === 'Too young' => 'waitlist',
                $visit['utm_campaign'] === 'summer_camp' => 'camp',
                mt_rand(1, 100) <= 15 => 'tour',
                default => 'enrollment',
            };

            $mother = mt_rand(1, 100) <= 78;
            $parent = ($mother ? $mothers[array_rand($mothers)] : $fathers[array_rand($fathers)]).' '.$lastNames[array_rand($lastNames)];
            $phone = '01'.[0, 1, 2, 5][mt_rand(0, 3)].str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

            $contactedAt = $status === 'new' ? null : $createdAt->addMinutes(mt_rand(20, 60 * 30));
            $contactedAt = $contactedAt?->greaterThan($this->now) ? $this->now->subMinutes(mt_rand(5, 60)) : $contactedAt;
            $closedAt = in_array($status, ['enrolled', 'lost'], true)
                ? CarbonImmutable::createFromTimestamp(min($this->now->getTimestamp(), $createdAt->addDays(mt_rand(4, max(5, min(30, $ageDays))))->getTimestamp()))
                : null;

            $lead = Lead::create([
                'parent_name' => $parent,
                'phone' => $phone,
                'whatsapp' => mt_rand(1, 100) <= 80 ? $phone : null,
                'email' => mt_rand(1, 100) <= 45 ? Str::slug($parent, '.').mt_rand(1, 99).'@gmail.com' : null,
                'child_name' => $children[array_rand($children)],
                'child_dob' => $dob->toDateString(),
                'child_age_months' => $months,
                'academic_year' => $this->academicYear,
                'classroom_id' => $classroom?->id,
                'branch_id' => $branchId,
                'camp_id' => $interest === 'camp' ? ($this->camps['summer-camp-2027'] ?? null) : null,
                'interest' => $interest,
                'preferred_tour_at' => mt_rand(1, 100) <= 40 ? $createdAt->addDays(mt_rand(2, 9))->setTime(mt_rand(9, 13), 0)->toDateTimeString() : null,
                'message' => $messages[array_rand($messages)],
                'heard_from' => $this->pick(['Facebook' => 40, 'A friend' => 25, 'Google' => 15, 'Instagram' => 10, '' => 10]) ?: null,
                'status' => $status,
                'priority' => $this->pick(['hot' => 30, 'warm' => 50, 'cold' => 20]),
                'lost_reason' => $status === 'lost' ? Lead::LOST_REASONS[array_rand(Lead::LOST_REASONS)] : null,
                'assigned_to' => $agent,
                'last_contacted_at' => $contactedAt?->toDateTimeString(),
                'enrolled_at' => $status === 'enrolled' ? $closedAt->toDateTimeString() : null,
                'channel' => 'website',
                'visitor_uuid' => $conversion['visitor'],
                'visit_id' => $visitIds[$conversion['visit']],
                'source' => $visit['source'],
                'utm_source' => $visit['utm_source'],
                'utm_medium' => $visit['utm_medium'],
                'utm_campaign' => $visit['utm_campaign'],
                'utm_content' => 'demo',
                'referrer' => $visit['referrer'],
                'landing_path' => $visit['landing_path'],
                'form_path' => '/enroll',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo data',
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => ($closedAt ?? $contactedAt ?? $createdAt)->toDateTimeString(),
            ]);
            $count++;

            DB::table('visitors')->where('id', $visitorIds[$conversion['visitor']])->update(['lead_id' => $lead->id]);

            // ---- Timeline ----
            $activity = function (string $type, CarbonImmutable $at, ?string $body, ?array $meta = null, ?int $userId = null) use (&$activities, $lead) {
                $activities[] = [
                    'lead_id' => $lead->id, 'user_id' => $userId, 'type' => $type, 'body' => $body,
                    'meta' => $meta ? json_encode($meta) : null,
                    'created_at' => $at->toDateTimeString(), 'updated_at' => $at->toDateTimeString(),
                ];
            };

            $activity('created', $createdAt, 'Submitted the website form', ['channel' => 'website', 'interest' => $interest]);
            if ($agent) {
                $activity('assigned', $createdAt->addSecond(), 'Automatically assigned to '.($agent === ($this->salesByBranch[$this->branches->first()->id] ?? 0) ? 'Hadayek Sales' : 'Zayed Sales'), ['to' => $agent, 'auto' => true]);
            }

            if ($contactedAt) {
                $activity('call', $contactedAt, $callNotes[array_rand($callNotes)], null, $agent);

                $path = match ($status) {
                    'contacted' => ['contacted'],
                    'tour_booked' => ['contacted', 'tour_booked'],
                    'toured' => ['contacted', 'tour_booked', 'toured'],
                    'enrolled' => ['contacted', 'tour_booked', 'toured', 'enrolled'],
                    'lost' => mt_rand(0, 1) ? ['contacted', 'lost'] : ['contacted', 'tour_booked', 'lost'],
                    default => [],
                };
                $end = $closedAt ?? CarbonImmutable::createFromTimestamp(min($this->now->getTimestamp(), $contactedAt->addDays(max(1, min(10, $ageDays)))->getTimestamp()));
                $span = max(60, $end->getTimestamp() - $contactedAt->getTimestamp());
                $from = 'new';
                foreach ($path as $i => $to) {
                    $at = $contactedAt->addSeconds((int) ($span * ($i / max(1, count($path) - 1 ?: 1))) + 30);
                    $at = $at->greaterThan($this->now) ? $this->now->subMinutes(mt_rand(1, 30)) : $at;
                    $activity('status_changed', $at, null, array_filter(['from' => $from, 'to' => $to, 'lost_reason' => $to === 'lost' ? $lead->lost_reason : null]), $agent);
                    $from = $to;
                }

                // A completed follow-up from the first contact.
                $followUps[] = [
                    'lead_id' => $lead->id, 'user_id' => $agent, 'created_by' => $agent, 'type' => mt_rand(0, 1) ? 'call' : 'whatsapp',
                    'due_at' => $contactedAt->toDateTimeString(), 'notes' => 'First contact',
                    'completed_at' => $contactedAt->toDateTimeString(), 'outcome' => $outcomes[array_rand($outcomes)],
                    'created_at' => $createdAt->toDateTimeString(), 'updated_at' => $contactedAt->toDateTimeString(),
                ];
            }

            if (! in_array($status, ['enrolled', 'lost'], true)) {
                $due = match ($this->pick(['overdue' => 30, 'today' => 30, 'later' => 40])) {
                    'overdue' => $this->now->subDays(mt_rand(1, 3))->setTime(mt_rand(10, 16), 0),
                    'today' => $this->now->startOfDay()->setTime(mt_rand(10, 17), [0, 30][mt_rand(0, 1)]),
                    default => $this->now->addDays(mt_rand(1, 5))->setTime(mt_rand(10, 16), 0),
                };
                $followUps[] = [
                    'lead_id' => $lead->id, 'user_id' => $agent, 'created_by' => $agent,
                    'type' => $status === 'tour_booked' ? 'tour' : (mt_rand(0, 1) ? 'call' : 'whatsapp'),
                    'due_at' => $due->toDateTimeString(),
                    'notes' => $status === 'tour_booked' ? 'School tour at the branch' : $this->pick(['Follow up on fees' => 1, 'Confirm tour date' => 1, 'Check if they decided' => 1, 'Call back after 5 pm' => 1]),
                    'completed_at' => null, 'outcome' => null,
                    'created_at' => ($contactedAt ?? $createdAt)->toDateTimeString(), 'updated_at' => ($contactedAt ?? $createdAt)->toDateTimeString(),
                ];
                DB::table('leads')->where('id', $lead->id)->update(['next_follow_up_at' => $due->toDateTimeString()]);
            }
        }

        foreach (array_chunk($activities, 500) as $chunk) {
            DB::table('lead_activities')->insert($chunk);
        }
        foreach (array_chunk($followUps, 500) as $chunk) {
            DB::table('follow_ups')->insert($chunk);
        }

        return $count;
    }
}
