<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Models\Lead;
use App\Models\Visitor;
use App\Support\Analytics\AnalyticsQuery;
use App\Support\Analytics\Journey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends AnalyticsController
{
    public const SEGMENTS = [
        'all' => 'All visitors',
        'lead' => 'Became lead',
        'abandoned' => 'Started form, not submitted',
        'tapped' => 'Tapped call / WhatsApp',
        'class_finder' => 'Used class finder',
        'returning' => 'Returning',
    ];

    public function index(Request $request)
    {
        $analytics = $this->analytics($request);
        $segment = array_key_exists($request->query('segment'), self::SEGMENTS) ? $request->query('segment') : 'all';
        $search = trim((string) $request->query('q', ''));

        $hasEvent = fn (Builder $q, array $names, ?callable $extra = null) => $q->whereExists(fn ($e) => $e
            ->from('tracking_events as te')->whereColumn('te.visitor_id', 'visitors.id')->whereIn('te.name', $names)
            ->when($extra, $extra));

        $visitors = Visitor::query()
            ->whereIn('visitors.id', $analytics->visits()->select('v.visitor_id'))
            ->with(['lead:id,reference,parent_name,phone,status'])
            ->withSum('pageViews as engaged_seconds', 'duration_seconds')
            ->when($segment === 'lead', fn ($q) => $q->whereNotNull('lead_id'))
            ->when($segment === 'returning', fn ($q) => $q->where('visits_count', '>', 1))
            ->when($segment === 'tapped', fn ($q) => $hasEvent($q, AnalyticsQuery::TAP_EVENTS))
            ->when($segment === 'class_finder', fn ($q) => $hasEvent($q, ['class_finder']))
            ->when($segment === 'abandoned', fn ($q) => $hasEvent($q, ['form_start'], fn ($e) => $e->where(fn ($w) => $w->whereNull('te.label')->orWhere('te.label', '!=', 'careers')))
                ->whereNull('lead_id')
                ->whereNotExists(fn ($e) => $e->from('tracking_events as ts')->whereColumn('ts.visitor_id', 'visitors.id')->where('ts.name', 'form_submit')))
            ->when($search !== '', function ($q) use ($search) {
                $digits = preg_replace('/\D/', '', $search);
                $q->whereIn('lead_id', Lead::withTrashed()->select('id')->where(fn ($w) => $w
                    ->where('reference', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%")
                    ->orWhere('child_name', 'like', "%{$search}%")
                    ->when(strlen($digits) >= 4, fn ($w) => $w->orWhere('phone', 'like', "%{$digits}%")->orWhere('whatsapp', 'like', "%{$digits}%"))));
            })
            ->orderByDesc('last_seen_at')
            ->paginate(25)
            ->withQueryString();

        $summaries = Journey::summaries($visitors->pluck('id')->all());

        return view('admin.analytics.visitors.index', [
            'analytics' => $analytics,
            'range' => $analytics->range,
            'visitors' => $visitors,
            'summaries' => $summaries,
            'segment' => $segment,
            'search' => $search,
            'campaigns' => AnalyticsQuery::campaignOptions(),
        ]);
    }

    public function show(Visitor $visitor)
    {
        $visitor->load(['lead' => fn ($q) => $q->withTrashed()->with(['assignee:id,name', 'branch:id,name', 'classroom:id,name,color'])]);

        $visits = $visitor->visits()
            ->with(['pageViews', 'events'])
            ->paginate(10)
            ->withQueryString();

        $totals = DB::table('page_views')->where('visitor_id', $visitor->id)
            ->selectRaw('COALESCE(SUM(duration_seconds), 0) AS engaged, COUNT(*) AS pageviews')->first();
        $eventCount = DB::table('tracking_events')->where('visitor_id', $visitor->id)->count();
        $firstVisit = $visitor->visits()->reorder('started_at')->first();

        return view('admin.analytics.visitors.show', [
            'visitor' => $visitor,
            'visits' => $visits,
            'engaged' => (int) $totals->engaged,
            'pageviews' => (int) $totals->pageviews,
            'eventCount' => $eventCount,
            'firstVisit' => $firstVisit,
            'badges' => Journey::badges(Journey::summaries([$visitor->id])->get($visitor->id), $visitor->lead),
        ]);
    }
}
