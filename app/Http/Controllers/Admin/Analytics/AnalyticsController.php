<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Support\Analytics\AnalyticsQuery;
use App\Support\Analytics\DateRange;
use App\Support\Analytics\Filters;
use Illuminate\Http\Request;

/** Shared range / filter parsing for the analytics screens (routes are limited to admin + sales manager). */
abstract class AnalyticsController extends Controller
{
    protected function analytics(Request $request): AnalyticsQuery
    {
        return new AnalyticsQuery(DateRange::fromRequest($request), Filters::fromRequest($request));
    }
}
