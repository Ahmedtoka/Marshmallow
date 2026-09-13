<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Support\Analytics\AnalyticsQuery;
use App\Support\Analytics\PageName;
use Illuminate\Http\Request;

class OverviewController extends AnalyticsController
{
    public function index(Request $request)
    {
        $analytics = $this->analytics($request);

        return view('admin.analytics.overview', [
            'analytics' => $analytics,
            'range' => $analytics->range,
            'kpis' => $analytics->kpis(),
            'previous' => $analytics->withRange($analytics->range->previous())->kpis(),
            'series' => $analytics->timeSeries(),
            'sources' => $analytics->sources(),
            'devices' => $analytics->devices(),
            'topPages' => $analytics->topPages(8)->each(fn ($p) => $p->name = PageName::for($p->path)),
            'classFinder' => $analytics->classFinder(),
            'actions' => $analytics->actions()->take(8),
            'newVsReturning' => $analytics->newVsReturning(),
            'activeNow' => $analytics->activeNow(),
            'campaigns' => AnalyticsQuery::campaignOptions(),
        ]);
    }
}
