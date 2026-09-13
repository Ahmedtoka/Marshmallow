<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Models\TrackingEvent;
use App\Support\Analytics\AnalyticsQuery;
use Illuminate\Http\Request;

class BehaviourController extends AnalyticsController
{
    public function sources(Request $request)
    {
        $analytics = $this->analytics($request);

        return view('admin.analytics.sources', [
            'analytics' => $analytics,
            'range' => $analytics->range,
            'sources' => $analytics->sources(),
            'campaignRows' => $analytics->campaigns(),
            'referrers' => $analytics->referrers(),
            'landing' => $analytics->landingBySource(),
            'campaigns' => AnalyticsQuery::campaignOptions(),
        ]);
    }

    public function pages(Request $request)
    {
        $analytics = $this->analytics($request);
        $sort = array_key_exists($request->query('sort'), AnalyticsQuery::PAGE_SORTS) ? $request->query('sort') : 'views';

        return view('admin.analytics.pages', [
            'analytics' => $analytics,
            'range' => $analytics->range,
            'pages' => $analytics->pages($sort),
            'sort' => $sort,
            'reach' => $analytics->sectionReach(),
            'campaigns' => AnalyticsQuery::campaignOptions(),
        ]);
    }

    public function actions(Request $request)
    {
        $analytics = $this->analytics($request);
        $actions = $analytics->actions();

        return view('admin.analytics.actions', [
            'analytics' => $analytics,
            'range' => $analytics->range,
            'actions' => $actions,
            'previous' => $analytics->withRange($analytics->range->previous())->actions()->keyBy('name'),
            'labels' => $analytics->actionLabels(),
            'series' => $analytics->contactSeries(),
            'names' => TrackingEvent::NAMES,
            'campaigns' => AnalyticsQuery::campaignOptions(),
        ]);
    }

    public function funnel(Request $request)
    {
        $analytics = $this->analytics($request);

        return view('admin.analytics.funnel', [
            'analytics' => $analytics,
            'range' => $analytics->range,
            'steps' => $analytics->funnel(),
            'previous' => collect($analytics->withRange($analytics->range->previous())->funnel())->keyBy('key'),
            'campaigns' => AnalyticsQuery::campaignOptions(),
        ]);
    }
}
