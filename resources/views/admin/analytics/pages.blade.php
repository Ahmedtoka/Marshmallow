@extends('layouts.admin')
@section('title', 'Pages')

@php
    use App\Support\Analytics\AnalyticsQuery;
    use App\Support\Analytics\Format;
@endphp

@section('content')
    <x-admin.page-header title="Pages" subtitle="What parents read, how long they stayed and where they left." />

    <x-admin.analytics.toolbar :range="$range" :campaigns="$campaigns">
        <div class="w-full sm:w-48">
            <label class="label" for="an-sort">Sort by</label>
            <select id="an-sort" name="sort" class="input" onchange="this.form.submit()">
                @foreach (AnalyticsQuery::PAGE_SORTS as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </x-admin.analytics.toolbar>

    <section class="card overflow-hidden mb-5">
        <div class="px-5 pt-5 pb-3">
            <h2 class="card-title">All pages</h2>
            <p class="text-xs text-muted mt-0.5">Time on page counts only while the tab is visible. "Led to lead" = visitors who viewed the page and later became a lead.</p>
        </div>
        @if ($pages->isEmpty())
            <x-admin.empty icon="file" title="No pageviews in this date range" />
        @else
            <div class="overflow-x-auto">
                <table class="table tabular-nums">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th class="text-right">Views</th>
                            <th class="text-right">Unique visitors</th>
                            <th class="text-right">Avg time</th>
                            <th>Avg scroll</th>
                            <th class="text-right">Entrances</th>
                            <th class="text-right">Exits</th>
                            <th class="text-right">Exit rate</th>
                            <th class="text-right">Led to lead</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pages as $page)
                            <tr>
                                <td class="min-w-48">
                                    <div class="font-bold">{{ $page->name }}</div>
                                    <div class="text-xs text-muted break-all">{{ $page->path }}</div>
                                </td>
                                <td class="text-right font-bold">{{ Format::num($page->views) }}</td>
                                <td class="text-right">{{ Format::num($page->visitors) }}</td>
                                <td class="text-right">{{ Format::duration($page->avg_time) }}</td>
                                <td class="whitespace-nowrap">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-16 h-1.5 rounded-full bg-canvas overflow-hidden"><span class="block h-full rounded-full bg-teal" style="width: {{ $page->avg_scroll }}%"></span></span>
                                        {{ Format::pct($page->avg_scroll, 0) }}
                                    </span>
                                </td>
                                <td class="text-right">{{ Format::num($page->entrances) }}</td>
                                <td class="text-right">{{ Format::num($page->exits) }}</td>
                                <td class="text-right">{{ Format::pct($page->exit_rate, 0) }}</td>
                                <td class="text-right font-bold text-brand-dark">{{ Format::num($page->leads) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="card card-pad">
        <div class="flex flex-wrap items-end justify-between gap-2 mb-4">
            <div>
                <h2 class="card-title">Homepage section reach</h2>
                <p class="text-xs text-muted mt-0.5">Share of homepage visits in which each section scrolled into view, in page order</p>
            </div>
            <p class="text-sm text-muted"><b class="text-ink tabular-nums">{{ Format::num($reach['home_visits']) }}</b> homepage visits</p>
        </div>
        @if ($reach['home_visits'] === 0)
            <p class="text-sm text-muted py-8 text-center">No homepage visits in this range.</p>
        @else
            <x-admin.analytics.bars color="#8479BD" :max="100" unit=""
                :rows="$reach['sections']->map(fn ($s) => ['label' => $s->name, 'value' => $s->reach, 'display' => Format::pct($s->reach, 0), 'sub' => '· '.Format::num($s->visits).' visits'.($s->visible ? '' : ' · hidden now'), 'color' => $s->visible ? '#8479BD' : '#C9C6DD'])" />
        @endif
    </section>
@endsection
