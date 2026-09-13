<?php

namespace App\Support\Analytics;

use App\Models\Classroom;
use App\Models\Section;
use App\Models\Visit;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Every aggregate query behind the analytics dashboards.
 *
 * Data model (see TrackingController): visitors ← visits ← page_views / tracking_events.
 * A visit is "in range" by started_at, a page view by entered_at, an event by created_at,
 * and a website lead by leads.created_at. Filters (source / device / campaign) always apply to the visit.
 */
class AnalyticsQuery
{
    public const TAP_EVENTS = ['call_click', 'whatsapp_click'];

    public function __construct(
        public readonly DateRange $range,
        public readonly Filters $filters = new Filters,
    ) {}

    public function withRange(DateRange $range): self
    {
        return new self($range, $this->filters);
    }

    /* ------------------------------------------------------------------
     | Base queries
     * ------------------------------------------------------------------ */

    public function visits(): Builder
    {
        return $this->filterVisits(DB::table('visits as v')->whereBetween('v.started_at', $this->range->bounds()));
    }

    public function filterVisits(Builder $query, string $alias = 'v'): Builder
    {
        return $query
            ->when($this->filters->source, fn ($q, $s) => $q->where("$alias.source", $s))
            ->when($this->filters->device, fn ($q, $d) => $q->where("$alias.device_type", $d))
            ->when($this->filters->campaign, fn ($q, $c) => $q->where("$alias.utm_campaign", $c));
    }

    public function pageViews(): Builder
    {
        $query = DB::table('page_views as pv')->whereBetween('pv.entered_at', $this->range->bounds());
        if ($this->filters->any()) {
            $this->filterVisits($query->join('visits as v', 'v.id', '=', 'pv.visit_id'));
        }

        return $query;
    }

    /** @param  list<string>|null  $names */
    public function events(?array $names = null): Builder
    {
        $query = DB::table('tracking_events as te')
            ->whereBetween('te.created_at', $this->range->bounds())
            ->when($names, fn ($q) => $q->whereIn('te.name', $names));
        if ($this->filters->any()) {
            $this->filterVisits($query->join('visits as v', 'v.id', '=', 'te.visit_id'));
        }

        return $query;
    }

    /** Website leads created in range. */
    public function leads(): Builder
    {
        return DB::table('leads as l')
            ->whereNull('l.deleted_at')
            ->where('l.channel', 'website')
            ->whereBetween('l.created_at', $this->range->bounds())
            ->when($this->filters->source, fn ($q, $s) => $s === 'direct'
                ? $q->where(fn ($w) => $w->where('l.source', 'direct')->orWhereNull('l.source'))
                : $q->where('l.source', $s))
            ->when($this->filters->campaign, fn ($q, $c) => $q->where('l.utm_campaign', $c))
            ->when($this->filters->device, fn ($q, $d) => $q->whereIn('l.visit_id', DB::table('visits')->select('id')->where('device_type', $d)));
    }

    /* ------------------------------------------------------------------
     | Overview
     * ------------------------------------------------------------------ */

    /** @return array<string, float|int> */
    public function kpis(): array
    {
        $v = $this->visits()
            ->selectRaw('COUNT(*) AS visits, COUNT(DISTINCT v.visitor_id) AS visitors, COALESCE(SUM(v.pageviews), 0) AS pageviews, COALESCE(AVG(v.is_bounce), 0) AS bounce')
            ->first();

        $engaged = (int) $this->filterVisits(
            DB::table('page_views as pv')->join('visits as v', 'v.id', '=', 'pv.visit_id')->whereBetween('v.started_at', $this->range->bounds())
        )->sum('pv.duration_seconds');

        $leads = $this->leads()->count();
        $taps = $this->events(self::TAP_EVENTS)->count();
        $visits = (int) $v->visits;

        return [
            'visitors' => (int) $v->visitors,
            'visits' => $visits,
            'pageviews' => (int) $v->pageviews,
            'avg_engaged' => $visits ? $engaged / $visits : 0,
            'bounce_rate' => (float) $v->bounce * 100,
            'leads' => $leads,
            'conversion' => Format::ratio($leads, $visits),
            'taps' => $taps,
        ];
    }

    /** Visits, visitors and leads per day (or per hour for a single-day range). */
    public function timeSeries(): array
    {
        $hourly = $this->range->isSingleDay();
        $bucketV = $hourly ? 'HOUR(v.started_at)' : 'DATE(v.started_at)';
        $bucketL = $hourly ? 'HOUR(l.created_at)' : 'DATE(l.created_at)';

        $visits = $this->visits()
            ->selectRaw("$bucketV AS b, COUNT(*) AS visits, COUNT(DISTINCT v.visitor_id) AS visitors")
            ->groupByRaw($bucketV)->get()->keyBy('b');
        $leads = $this->leads()->selectRaw("$bucketL AS b, COUNT(*) AS n")->groupByRaw($bucketL)->pluck('n', 'b');

        $labels = $v = $u = $l = [];
        if ($hourly) {
            foreach (range(0, 23) as $h) {
                $labels[] = sprintf('%02d:00', $h);
                $v[] = (int) ($visits[$h]->visits ?? 0);
                $u[] = (int) ($visits[$h]->visitors ?? 0);
                $l[] = (int) ($leads[$h] ?? 0);
            }
        } else {
            foreach ($this->range->dates() as $date) {
                $key = $date->toDateString();
                $labels[] = $date->format('j M');
                $v[] = (int) ($visits[$key]->visits ?? 0);
                $u[] = (int) ($visits[$key]->visitors ?? 0);
                $l[] = (int) ($leads[$key] ?? 0);
            }
        }

        return ['labels' => $labels, 'visits' => $v, 'visitors' => $u, 'leads' => $l, 'hourly' => $hourly];
    }

    public function activeNow(): Collection
    {
        return Visit::with('visitor:id,lead_id,visits_count')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->latest('last_activity_at')
            ->limit(12)
            ->get();
    }

    public function devices(): Collection
    {
        return $this->visits()
            ->selectRaw("COALESCE(v.device_type, 'desktop') AS device, COUNT(*) AS visits")
            ->groupBy('v.device_type')
            ->orderByDesc('visits')
            ->get();
    }

    /** @return array{new: int, returning: int} */
    public function newVsReturning(): array
    {
        $row = DB::table('visitors as vr')
            ->whereIn('vr.id', $this->visits()->select('v.visitor_id'))
            ->selectRaw('COUNT(*) AS total, COALESCE(SUM(vr.first_seen_at < ?), 0) AS ret', [$this->range->from->toDateTimeString()])
            ->first();

        return ['new' => (int) $row->total - (int) $row->ret, 'returning' => (int) $row->ret];
    }

    /** Class finder results, coloured with each class's colour. */
    public function classFinder(): Collection
    {
        $classes = Classroom::orderBy('min_months')->get(['name', 'color', 'min_months'])->keyBy('name');

        $rows = $this->events(['class_finder'])
            ->selectRaw('te.label, COUNT(*) AS n, COUNT(DISTINCT te.visitor_id) AS visitors')
            ->groupBy('te.label')
            ->get()
            ->keyBy('label');

        // Class order first (youngest → oldest), then "Too young"/"Too old"/anything else.
        $order = collect(['Too young'])->merge($classes->keys())->push('Too old');

        return $rows->sortBy(fn ($r) => ($i = $order->search($r->label)) === false ? 999 : $i)
            ->map(fn ($r) => (object) [
                'label' => $r->label ?: 'Unknown',
                'n' => (int) $r->n,
                'visitors' => (int) $r->visitors,
                'color' => $classes[$r->label]->color ?? '#9B98B8',
            ])->values();
    }

    /** Event names (excluding section views) with totals and unique visitors. */
    public function actions(): Collection
    {
        return $this->events()
            ->where('te.name', '!=', 'section_view')
            ->selectRaw('te.name, COUNT(*) AS n, COUNT(DISTINCT te.visitor_id) AS visitors')
            ->groupBy('te.name')
            ->orderByDesc('n')
            ->get();
    }

    public function topPages(int $limit = 8): Collection
    {
        return $this->pageViews()
            ->selectRaw('pv.path, COUNT(*) AS views, COUNT(DISTINCT pv.visitor_id) AS visitors')
            ->groupBy('pv.path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /* ------------------------------------------------------------------
     | Sources
     * ------------------------------------------------------------------ */

    public function sources(): Collection
    {
        $visits = $this->visits()
            ->selectRaw('v.source, COUNT(*) AS visits, COUNT(DISTINCT v.visitor_id) AS visitors, AVG(v.is_bounce) * 100 AS bounce, AVG(v.pageviews) AS ppv')
            ->groupBy('v.source')->get()->keyBy('source');

        $engaged = $this->filterVisits(
            DB::table('page_views as pv')->join('visits as v', 'v.id', '=', 'pv.visit_id')->whereBetween('v.started_at', $this->range->bounds())
        )->selectRaw('v.source, SUM(pv.duration_seconds) AS s')->groupBy('v.source')->pluck('s', 'source');

        $leads = $this->leads()
            ->selectRaw("l.source, COUNT(*) AS leads, SUM(l.status = 'enrolled') AS enrolled")
            ->groupBy('l.source')->get()
            ->groupBy(fn ($r) => $r->source ?: 'direct')
            ->map(fn ($g) => (object) ['leads' => $g->sum('leads'), 'enrolled' => $g->sum('enrolled')]);

        return $visits->keys()->merge($leads->keys())->unique()
            ->map(function ($source) use ($visits, $engaged, $leads) {
                $v = $visits[$source] ?? null;
                $n = (int) ($v->visits ?? 0);
                $leadCount = (int) ($leads[$source]->leads ?? 0);

                return (object) [
                    'source' => $source,
                    'label' => Visit::SOURCES[$source] ?? ucfirst((string) $source),
                    'visits' => $n,
                    'visitors' => (int) ($v->visitors ?? 0),
                    'bounce' => (float) ($v->bounce ?? 0),
                    'avg_engaged' => $n ? (int) ($engaged[$source] ?? 0) / $n : 0,
                    'ppv' => (float) ($v->ppv ?? 0),
                    'leads' => $leadCount,
                    'enrolled' => (int) ($leads[$source]->enrolled ?? 0),
                    'conversion' => Format::ratio($leadCount, $n),
                ];
            })
            ->sortByDesc('visits')->values();
    }

    public function campaigns(int $limit = 25): Collection
    {
        $visits = $this->visits()
            ->where(fn ($q) => $q->whereNotNull('v.utm_campaign')->orWhereNotNull('v.utm_source'))
            ->selectRaw('v.utm_source, v.utm_medium, v.utm_campaign, COUNT(*) AS visits, COUNT(DISTINCT v.visitor_id) AS visitors')
            ->groupBy('v.utm_source', 'v.utm_medium', 'v.utm_campaign')
            ->orderByDesc('visits')->limit($limit)->get();

        $leads = $this->leads()
            ->where(fn ($q) => $q->whereNotNull('l.utm_campaign')->orWhereNotNull('l.utm_source'))
            ->selectRaw("l.utm_source, l.utm_medium, l.utm_campaign, COUNT(*) AS leads, SUM(l.status = 'enrolled') AS enrolled")
            ->groupBy('l.utm_source', 'l.utm_medium', 'l.utm_campaign')->get()
            ->keyBy(fn ($r) => $r->utm_source.'|'.$r->utm_medium.'|'.$r->utm_campaign);

        return $visits->map(function ($r) use ($leads) {
            $lead = $leads[$r->utm_source.'|'.$r->utm_medium.'|'.$r->utm_campaign] ?? null;
            $r->leads = (int) ($lead->leads ?? 0);
            $r->enrolled = (int) ($lead->enrolled ?? 0);
            $r->conversion = Format::ratio($r->leads, $r->visits);

            return $r;
        });
    }

    /** Distinct campaign names seen in the last year, for the filter dropdown. */
    public static function campaignOptions(): array
    {
        return DB::table('visits')
            ->whereNotNull('utm_campaign')
            ->where('started_at', '>=', now()->subYear())
            ->distinct()->orderBy('utm_campaign')->limit(100)
            ->pluck('utm_campaign')->mapWithKeys(fn ($c) => [$c => $c])->all();
    }

    public function referrers(int $limit = 15): Collection
    {
        return $this->visits()
            ->whereNotNull('v.referrer_host')
            ->selectRaw('v.referrer_host, COUNT(*) AS visits, COUNT(DISTINCT v.visitor_id) AS visitors, SUM(v.converted) AS leads')
            ->groupBy('v.referrer_host')
            ->orderByDesc('visits')->limit($limit)->get();
    }

    /** Top landing pages for each source. */
    public function landingBySource(int $perSource = 4): Collection
    {
        return $this->visits()
            ->selectRaw('v.source, v.landing_path, COUNT(*) AS visits, SUM(v.converted) AS leads, AVG(v.is_bounce) * 100 AS bounce')
            ->groupBy('v.source', 'v.landing_path')
            ->get()
            ->groupBy('source')
            ->map(fn ($rows) => $rows->sortByDesc('visits')->take($perSource)->values())
            ->sortByDesc(fn ($rows) => $rows->sum('visits'));
    }

    /* ------------------------------------------------------------------
     | Pages
     * ------------------------------------------------------------------ */

    public const PAGE_SORTS = [
        'views' => 'Views',
        'visitors' => 'Unique visitors',
        'time' => 'Avg time on page',
        'scroll' => 'Avg scroll depth',
        'entrances' => 'Entrances',
        'exit_rate' => 'Exit rate',
        'leads' => 'Led to lead',
    ];

    public function pages(string $sort = 'views', int $limit = 100): Collection
    {
        $views = $this->pageViews()
            ->selectRaw('pv.path, COUNT(*) AS views, COUNT(DISTINCT pv.visitor_id) AS visitors, AVG(pv.duration_seconds) AS avg_time, AVG(pv.max_scroll) AS avg_scroll')
            ->groupBy('pv.path')->get();

        $entrances = $this->visits()->selectRaw('v.landing_path AS path, COUNT(*) AS n')->groupBy('v.landing_path')->pluck('n', 'path');
        $exits = $this->visits()->selectRaw('v.exit_path AS path, COUNT(*) AS n')->groupBy('v.exit_path')->pluck('n', 'path');
        $leads = $this->pageViews()
            ->join('visitors as vr', 'vr.id', '=', 'pv.visitor_id')
            ->whereNotNull('vr.lead_id')
            ->selectRaw('pv.path, COUNT(DISTINCT pv.visitor_id) AS n')
            ->groupBy('pv.path')->pluck('n', 'path');

        $rows = $views->map(function ($r) use ($entrances, $exits, $leads) {
            $r->name = PageName::for($r->path);
            $r->views = (int) $r->views;
            $r->visitors = (int) $r->visitors;
            $r->avg_time = (float) $r->avg_time;
            $r->avg_scroll = (float) $r->avg_scroll;
            $r->entrances = (int) ($entrances[$r->path] ?? 0);
            $r->exits = (int) ($exits[$r->path] ?? 0);
            $r->exit_rate = Format::ratio($r->exits, $r->views);
            $r->leads = (int) ($leads[$r->path] ?? 0);

            return $r;
        });

        $key = match ($sort) {
            'visitors' => 'visitors', 'time' => 'avg_time', 'scroll' => 'avg_scroll', 'entrances' => 'entrances',
            'exit_rate' => 'exit_rate', 'leads' => 'leads', default => 'views',
        };

        return $rows->sortByDesc($key)->take($limit)->values();
    }

    /** For each homepage section: share of homepage visits in which it was seen. */
    public function sectionReach(): array
    {
        $homeVisits = (int) $this->pageViews()->where('pv.path', '/')->distinct()->count('pv.visit_id');

        $seen = $this->events(['section_view'])
            ->selectRaw('te.label, COUNT(DISTINCT te.visit_id) AS n')
            ->groupBy('te.label')->pluck('n', 'label');

        try {
            $sections = Section::map();
        } catch (\Throwable) {
            $sections = collect();
        }

        $keys = $sections->keys()->merge($seen->keys())->filter()->unique()->values();

        $rows = $keys->map(fn ($key) => (object) [
            'key' => $key,
            'name' => PageName::section($key),
            'title' => $sections[$key]->title ?? null,
            'visible' => (bool) ($sections[$key]->is_visible ?? true),
            'visits' => (int) ($seen[$key] ?? 0),
            'reach' => min(100, Format::ratio($seen[$key] ?? 0, $homeVisits)),
        ]);

        return ['home_visits' => $homeVisits, 'sections' => $rows];
    }

    /* ------------------------------------------------------------------
     | Actions
     * ------------------------------------------------------------------ */

    /** Top labels per event name, e.g. calls per branch or FAQ questions opened. */
    public function actionLabels(int $perName = 10): Collection
    {
        return $this->events()
            ->where('te.name', '!=', 'section_view')
            ->selectRaw('te.name, te.label, COUNT(*) AS n, COUNT(DISTINCT te.visitor_id) AS visitors')
            ->groupBy('te.name', 'te.label')
            ->get()
            ->groupBy('name')
            ->map(fn ($rows) => $rows->sortByDesc('n')->take($perName)->values());
    }

    /** Calls, WhatsApp taps and enrollment form submits per day. */
    public function contactSeries(): array
    {
        $hourly = $this->range->isSingleDay();
        $bucket = $hourly ? 'HOUR(te.created_at)' : 'DATE(te.created_at)';

        $rows = $this->events(['call_click', 'whatsapp_click', 'form_submit'])
            ->where(fn ($q) => $q->where('te.name', '!=', 'form_submit')->orWhereNull('te.label')->orWhere('te.label', '!=', 'careers'))
            ->selectRaw("$bucket AS b, te.name, COUNT(*) AS n")
            ->groupByRaw("$bucket, te.name")
            ->get()
            ->groupBy('name')
            ->map(fn ($g) => $g->pluck('n', 'b'));

        $buckets = $hourly
            ? collect(range(0, 23))->mapWithKeys(fn ($h) => [$h => sprintf('%02d:00', $h)])
            : collect($this->range->dates())->mapWithKeys(fn ($d) => [$d->toDateString() => $d->format('j M')]);

        $series = fn ($name) => $buckets->keys()->map(fn ($b) => (int) ($rows[$name][$b] ?? 0))->all();

        return [
            'labels' => $buckets->values()->all(),
            'calls' => $series('call_click'),
            'whatsapp' => $series('whatsapp_click'),
            'submits' => $series('form_submit'),
        ];
    }

    /* ------------------------------------------------------------------
     | Funnel
     * ------------------------------------------------------------------ */

    /** @return list<array{key: string, label: string, hint: string, count: int}> */
    public function funnel(): array
    {
        $visits = (int) $this->visits()->count();
        $engaged = (int) $this->visits()->where('v.is_bounce', false)->count();

        $classInterest = (int) $this->visits()
            ->where('v.is_bounce', false)
            ->where(fn ($q) => $q
                ->whereExists(fn ($e) => $e->from('tracking_events as te')->whereColumn('te.visit_id', 'v.id')->where('te.name', 'class_finder'))
                ->orWhereExists(fn ($p) => $p->from('page_views as pv')->whereColumn('pv.visit_id', 'v.id')->where('pv.path', 'like', '/classes%')))
            ->count();

        $formStarted = (int) $this->visits()
            ->whereExists(fn ($e) => $e->from('tracking_events as te')->whereColumn('te.visit_id', 'v.id')->where('te.name', 'form_start')
                ->where(fn ($w) => $w->where('te.label', 'enroll')->orWhere('te.path', '/enroll')))
            ->count();

        $leads = $this->leads()
            ->selectRaw("COUNT(*) AS submitted,
                COALESCE(SUM(l.status <> 'new'), 0) AS contacted,
                COALESCE(SUM(l.status IN ('tour_booked', 'toured', 'enrolled')), 0) AS tour_booked,
                COALESCE(SUM(l.status IN ('toured', 'enrolled')), 0) AS toured,
                COALESCE(SUM(l.status = 'enrolled'), 0) AS enrolled")
            ->first();

        return [
            ['key' => 'visits', 'label' => 'Visits', 'hint' => 'Every visit to the website', 'count' => $visits],
            ['key' => 'engaged', 'label' => 'Engaged visits', 'hint' => 'Viewed 2+ pages or tapped something', 'count' => $engaged],
            ['key' => 'class_interest', 'label' => 'Looked at classes', 'hint' => 'Used the class finder or opened a class page', 'count' => $classInterest],
            ['key' => 'form_start', 'label' => 'Started enrollment form', 'hint' => 'Clicked into the enrollment form', 'count' => $formStarted],
            ['key' => 'submitted', 'label' => 'Submitted (website leads)', 'hint' => 'Lead created from the website form', 'count' => (int) $leads->submitted],
            ['key' => 'contacted', 'label' => 'Contacted', 'hint' => 'Moved past "New" by the sales team', 'count' => (int) $leads->contacted],
            ['key' => 'tour_booked', 'label' => 'Tour booked', 'hint' => 'Tour booked, toured or enrolled', 'count' => (int) $leads->tour_booked],
            ['key' => 'toured', 'label' => 'Toured', 'hint' => 'Visited the branch or enrolled', 'count' => (int) $leads->toured],
            ['key' => 'enrolled', 'label' => 'Enrolled', 'hint' => 'Became a Marshmallow family', 'count' => (int) $leads->enrolled],
        ];
    }
}
