<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Models\TrackingEvent;
use App\Models\Visit;
use App\Models\Visitor;
use App\Support\TrafficSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Receives beacons from resources/js/tracker.js. Always answers quickly and never throws at the visitor.
 */
class TrackingController extends Controller
{
    /** Events that mean the visitor engaged, so the visit no longer counts as a bounce. */
    private const ENGAGED = ['call_click', 'whatsapp_click', 'map_click', 'email_click', 'cta_click', 'class_finder', 'form_start', 'form_submit', 'gallery_open', 'faq_open', 'video_play'];

    public function collect(Request $request): JsonResponse
    {
        $data = $request->json()->all() ?: $request->all();
        $ua = $request->userAgent();

        // No session on this route (see routes/web.php): dashboard users never get the tracker in the first place.
        if (TrafficSource::isBot($ua) || ! Str::isUuid($data['vid'] ?? '') || ! Str::isUuid($data['sid'] ?? '')) {
            return response()->json(['ok' => false]);
        }

        $now = now();
        $visitor = $this->visitor($data['vid'], $ua, $now);
        $type = $data['type'] ?? '';

        if ($type === 'pageview') {
            $visit = $this->visit($visitor, $data, $request, $now);
            $path = Str::limit((string) ($data['path'] ?? '/'), 250, '');

            $pageView = PageView::create([
                'visit_id' => $visit->id,
                'visitor_id' => $visitor->id,
                'path' => $path,
                'title' => Str::limit((string) ($data['title'] ?? ''), 250, '') ?: null,
                'query' => Str::limit((string) ($data['query'] ?? ''), 250, '') ?: null,
                'entered_at' => $now,
            ]);

            $visit->pageviews++;
            $visit->exit_path = $path;
            $visit->last_activity_at = $now;
            $visit->duration_seconds = (int) $visit->started_at->diffInSeconds($now);
            if ($visit->pageviews > 1) {
                $visit->is_bounce = false;
            }
            $visit->save();

            $visitor->increment('pageviews_count', 1, ['last_seen_at' => $now]);

            return response()->json(['ok' => true, 'pv' => $pageView->id]);
        }

        $visit = Visit::where('uuid', $data['sid'])->where('visitor_id', $visitor->id)->first();
        if (! $visit) {
            return response()->json(['ok' => false]);
        }

        $pageView = isset($data['pv']) ? PageView::where('id', (int) $data['pv'])->where('visit_id', $visit->id)->first() : null;

        if ($type === 'ping' && $pageView) {
            $pageView->update([
                'duration_seconds' => max($pageView->duration_seconds, min(3600, (int) ($data['duration'] ?? 0))),
                'max_scroll' => max($pageView->max_scroll, min(100, (int) ($data['scroll'] ?? 0))),
            ]);
            $engaged = (int) PageView::where('visit_id', $visit->id)->sum('duration_seconds');
            $visit->update([
                'last_activity_at' => $now,
                'duration_seconds' => max($visit->duration_seconds, $engaged),
            ]);
        }

        if ($type === 'event' && ! empty($data['name'])) {
            $name = Str::limit(preg_replace('/[^a-z0-9_]/', '', strtolower((string) $data['name'])), 60, '');
            TrackingEvent::create([
                'visit_id' => $visit->id,
                'visitor_id' => $visitor->id,
                'page_view_id' => $pageView?->id,
                'name' => $name,
                'label' => isset($data['label']) ? Str::limit((string) $data['label'], 250, '') : null,
                'value' => isset($data['value']) ? Str::limit((string) $data['value'], 250, '') : null,
                'properties' => is_array($data['props'] ?? null) ? array_slice($data['props'], 0, 20) : null,
                'path' => Str::limit((string) ($data['path'] ?? $pageView?->path), 250, '') ?: null,
                'created_at' => $now,
            ]);

            $visit->events_count++;
            $visit->last_activity_at = $now;
            if (in_array($name, self::ENGAGED, true)) {
                $visit->is_bounce = false;
            }
            $visit->save();
        }

        return response()->json(['ok' => true]);
    }

    private function visitor(string $uuid, ?string $ua, $now): Visitor
    {
        return Visitor::firstOrCreate(['uuid' => $uuid], TrafficSource::parseUserAgent($ua) + [
            'first_seen_at' => $now,
            'last_seen_at' => $now,
        ]);
    }

    private function visit(Visitor $visitor, array $data, Request $request, $now): Visit
    {
        $existing = Visit::where('uuid', $data['sid'])->first();
        if ($existing && $existing->visitor_id === $visitor->id) {
            return $existing;
        }

        $utm = array_intersect_key(is_array($data['utm'] ?? null) ? $data['utm'] : [], array_flip(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']));
        $utm = array_map(fn ($v) => Str::limit((string) $v, 190, ''), $utm);
        $clickId = in_array($data['click_id'] ?? null, ['gclid', 'fbclid'], true) ? $data['click_id'] : null;
        $referrer = Str::limit((string) ($data['referrer'] ?? ''), 1000, '') ?: null;
        $referrerHost = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;
        $ownHost = $request->getHost();
        if ($referrerHost === $ownHost) {
            $referrer = $referrerHost = null;
        }
        $source = TrafficSource::classify($referrer, $utm, $clickId, $ownHost);
        $path = Str::limit((string) ($data['path'] ?? '/'), 250, '');

        $visit = Visit::create(TrafficSource::parseUserAgent($request->userAgent()) + $utm + [
            'visitor_id' => $visitor->id,
            // Set counters explicitly: $visit->pageviews on a fresh model would otherwise resolve the pageViews() relation.
            'pageviews' => 0,
            'events_count' => 0,
            'duration_seconds' => 0,
            'is_bounce' => true,
            'converted' => false,
            // A sid reused by a different visitor (copied cookie) gets a fresh id instead of merging journeys.
            'uuid' => $existing ? (string) Str::uuid() : $data['sid'],
            'started_at' => $now,
            'last_activity_at' => $now,
            'landing_path' => $path,
            'exit_path' => $path,
            'referrer' => $referrer,
            'referrer_host' => $referrerHost,
            'source' => $source,
            'click_id' => $clickId,
            'screen' => Str::limit((string) ($data['screen'] ?? ''), 20, '') ?: null,
            'language' => Str::limit((string) ($data['lang'] ?? ''), 20, '') ?: null,
            'country' => $request->header('CF-IPCountry') ?: null,
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
        ]);

        $visitor->visits_count++;
        $visitor->last_seen_at = $now;
        if (! $visitor->first_source) {
            $visitor->fill([
                'first_source' => $source,
                'first_referrer_host' => $referrerHost,
                'first_utm_source' => $utm['utm_source'] ?? null,
                'first_utm_campaign' => $utm['utm_campaign'] ?? null,
                'first_landing_path' => $path,
                'country' => $visit->country,
            ]);
        }
        $visitor->save();

        return $visit;
    }
}
