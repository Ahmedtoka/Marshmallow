@extends('layouts.admin')
@section('title', 'Website analytics')

@php
    use App\Models\TrackingEvent;
    use App\Models\Visit;
    use App\Support\Analytics\Format;
    use App\Support\Analytics\PageName;

    $C = ['visits' => '#2CBCC9', 'visitors' => '#8479BD', 'leads' => '#E8177F', 'pageviews' => '#E8A317', 'taps' => '#7FA82A'];
    $hasData = $kpis['visits'] > 0 || $kpis['leads'] > 0;
    $unit = $series['hourly'] ? 'hour' : 'day';

    $lineConfig = [
        'type' => 'line',
        'data' => [
            'labels' => $series['labels'],
            'datasets' => [
                ['label' => 'Visits', 'data' => $series['visits'], 'borderColor' => $C['visits'], 'backgroundColor' => 'rgba(44,188,201,0.12)', 'fill' => true, 'tension' => 0.3, 'pointRadius' => 0, 'pointHoverRadius' => 4, 'borderWidth' => 2, 'yAxisID' => 'y'],
                ['label' => 'Visitors', 'data' => $series['visitors'], 'borderColor' => $C['visitors'], 'backgroundColor' => $C['visitors'], 'tension' => 0.3, 'pointRadius' => 0, 'pointHoverRadius' => 4, 'borderWidth' => 2, 'borderDash' => [5, 4], 'yAxisID' => 'y'],
                ['label' => 'Website leads', 'data' => $series['leads'], 'borderColor' => $C['leads'], 'backgroundColor' => $C['leads'], 'tension' => 0.3, 'pointRadius' => 2.5, 'pointHoverRadius' => 5, 'borderWidth' => 2, 'yAxisID' => 'y1'],
            ],
        ],
        'options' => [
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'scales' => [
                'x' => ['grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8, 'maxRotation' => 0, 'autoSkip' => true]],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => ['color' => '#EFEDF7'], 'title' => ['display' => true, 'text' => 'Visits / visitors']],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'ticks' => ['precision' => 0], 'grid' => ['drawOnChartArea' => false], 'title' => ['display' => true, 'text' => 'Leads', 'color' => $C['leads']]],
            ],
        ],
    ];

    $topSources = $sources->take(8);
    $sourceConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $topSources->pluck('label')->all(),
            'datasets' => [
                ['label' => 'Visits', 'data' => $topSources->pluck('visits')->all(), 'backgroundColor' => $C['visits'], 'borderRadius' => 4, 'xAxisID' => 'x', 'barPercentage' => 0.8, 'categoryPercentage' => 0.8],
                ['label' => 'Leads', 'data' => $topSources->pluck('leads')->all(), 'backgroundColor' => $C['leads'], 'borderRadius' => 4, 'xAxisID' => 'x1', 'barPercentage' => 0.8, 'categoryPercentage' => 0.8],
            ],
        ],
        'options' => [
            'indexAxis' => 'y',
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0, 'maxTicksLimit' => 5], 'grid' => ['color' => '#EFEDF7'], 'title' => ['display' => true, 'text' => 'Visits', 'color' => '#1B8C96']],
                'x1' => ['beginAtZero' => true, 'position' => 'top', 'ticks' => ['precision' => 0, 'maxTicksLimit' => 5], 'grid' => ['drawOnChartArea' => false], 'title' => ['display' => true, 'text' => 'Leads', 'color' => $C['leads']]],
                'y' => ['grid' => ['display' => false]],
            ],
        ],
    ];

    $deviceLabels = ['mobile' => 'Mobile', 'tablet' => 'Tablet', 'desktop' => 'Desktop'];
    $deviceColors = ['mobile' => '#2CBCC9', 'desktop' => '#8479BD', 'tablet' => '#E8A317'];
    $deviceTotal = max(1, $devices->sum('visits'));
    $deviceConfig = [
        'type' => 'doughnut',
        'data' => [
            'labels' => $devices->map(fn ($d) => ($deviceLabels[$d->device] ?? ucfirst($d->device)).' '.Format::pct($d->visits / $deviceTotal * 100, 0))->all(),
            'datasets' => [[
                'data' => $devices->pluck('visits')->all(),
                'backgroundColor' => $devices->map(fn ($d) => $deviceColors[$d->device] ?? '#9B98B8')->all(),
                'borderWidth' => 2, 'borderColor' => '#ffffff',
            ]],
        ],
        'options' => ['cutout' => '64%', 'plugins' => ['tooltip' => ['mode' => 'nearest', 'intersect' => true]]],
    ];

    $nvr = $newVsReturning;
    $nvrTotal = max(1, $nvr['new'] + $nvr['returning']);
@endphp

@section('content')
    <x-admin.page-header title="Website analytics" subtitle="What parents do on the website — from first visit to enrollment." />

    <x-admin.analytics.toolbar :range="$range" :campaigns="$campaigns" />

    {{-- Active now --}}
    <section class="card card-pad mb-5" aria-labelledby="active-now">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h2 id="active-now" class="card-title flex items-center gap-2">
                <span class="relative flex size-2.5">
                    @if ($activeNow->isNotEmpty())<span class="animate-ping absolute inline-flex size-full rounded-full bg-lime opacity-60"></span>@endif
                    <span @class(['relative inline-flex rounded-full size-2.5', 'bg-lime' => $activeNow->isNotEmpty(), 'bg-line' => $activeNow->isEmpty()])></span>
                </span>
                Active now
            </h2>
            <span class="text-sm text-muted"><b class="text-ink tabular-nums">{{ $activeNow->count() }}</b> {{ Str::plural('visit', $activeNow->count()) }} active in the last 5 minutes</span>
        </div>
        @if ($activeNow->isEmpty())
            <p class="text-sm text-muted">Nobody is browsing the website right now.</p>
        @else
            <ul class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($activeNow as $visit)
                    <li>
                        <a href="{{ route('admin.analytics.visitors.show', $visit->visitor_id) }}" class="flex items-center gap-3 rounded-xl border border-line px-3 py-2 hover:border-grape/50 hover:bg-canvas/60">
                            <x-icon :name="$visit->device_type === 'desktop' ? 'monitor' : 'smartphone'" class="size-4 text-muted" />
                            <span class="min-w-0 flex-1">
                                <span class="block font-bold truncate">{{ PageName::for($visit->exit_path) }}</span>
                                <span class="block text-xs text-muted truncate">{{ $visit->sourceLabel() }} · {{ $visit->pageviews }} {{ Str::plural('page', $visit->pageviews) }} · started {{ $visit->started_at->diffForHumans(short: true) }}</span>
                            </span>
                            @if ($visit->visitor?->lead_id)<span class="badge badge-pink">Lead</span>@elseif (($visit->visitor?->visits_count ?? 1) > 1)<span class="badge badge-muted">Returning</span>@endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <x-admin.analytics.kpi label="Visitors" :color="$C['visitors']" :value="Format::num($kpis['visitors'])" :current="$kpis['visitors']" :previous="$previous['visitors']" />
        <x-admin.analytics.kpi label="Visits" :color="$C['visits']" :value="Format::num($kpis['visits'])" :current="$kpis['visits']" :previous="$previous['visits']" />
        <x-admin.analytics.kpi label="Pageviews" :color="$C['pageviews']" :value="Format::num($kpis['pageviews'])" :current="$kpis['pageviews']" :previous="$previous['pageviews']" />
        <x-admin.analytics.kpi label="Avg engaged time / visit" :value="Format::duration($kpis['avg_engaged'])" :current="$kpis['avg_engaged']" :previous="$previous['avg_engaged']" />
        <x-admin.analytics.kpi label="Bounce rate" :value="Format::pct($kpis['bounce_rate'])" :current="$kpis['bounce_rate']" :previous="$previous['bounce_rate']" invert />
        <x-admin.analytics.kpi label="Website leads" :color="$C['leads']" :value="Format::num($kpis['leads'])" :current="$kpis['leads']" :previous="$previous['leads']" />
        <x-admin.analytics.kpi label="Visit → lead conversion" :value="Format::pct($kpis['conversion'], 2)" :current="$kpis['conversion']" :previous="$previous['conversion']" />
        <x-admin.analytics.kpi label="Call + WhatsApp taps" :color="$C['taps']" :value="Format::num($kpis['taps'])" :current="$kpis['taps']" :previous="$previous['taps']" />
    </div>

    @if (! $hasData)
        <div class="card mb-5">
            <x-admin.empty icon="chart" title="No visits in this date range" text="Try a longer range, or clear the filters. Visits appear here a few seconds after someone opens the website." />
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3 mb-5">
        <x-admin.analytics.chart class="lg:col-span-2" title="Visits, visitors and website leads per {{ $unit }}"
            subtitle="Leads use the right-hand axis" :config="$lineConfig" :height="290" :empty="! $hasData" />
        <x-admin.analytics.chart title="Visits and leads by traffic source" subtitle="Top {{ $topSources->count() }} sources in this range"
            :config="$sourceConfig" :height="max(220, $topSources->count() * 38 + 90)" :empty="$sources->isEmpty()" />
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3 mb-5">
        <x-admin.analytics.chart title="Visits by device" subtitle="Share of visits from phones, tablets and computers"
            :config="$deviceConfig" :height="230" :empty="$devices->isEmpty()" />

        <div class="card card-pad">
            <h2 class="card-title">New vs returning visitors</h2>
            <p class="text-xs text-muted mt-0.5 mb-4">Returning = first seen before this date range</p>
            @if ($nvr['new'] + $nvr['returning'] === 0)
                <p class="text-sm text-muted py-10 text-center">No visitors in this date range.</p>
            @else
                <div class="flex h-4 rounded-full overflow-hidden bg-canvas mb-4" role="img" aria-label="{{ Format::pct($nvr['new'] / $nvrTotal * 100, 0) }} new, {{ Format::pct($nvr['returning'] / $nvrTotal * 100, 0) }} returning">
                    <div style="width: {{ $nvr['new'] / $nvrTotal * 100 }}%; background: #2CBCC9"></div>
                    <div style="width: {{ $nvr['returning'] / $nvrTotal * 100 }}%; background: #8479BD"></div>
                </div>
                <dl class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-canvas/70 p-3">
                        <dt class="stat-label flex items-center gap-1.5"><span class="size-2 rounded-full bg-teal"></span> New</dt>
                        <dd class="font-display text-2xl tabular-nums mt-1">{{ Format::num($nvr['new']) }}</dd>
                        <dd class="text-xs text-muted tabular-nums">{{ Format::pct($nvr['new'] / $nvrTotal * 100) }}</dd>
                    </div>
                    <div class="rounded-xl bg-canvas/70 p-3">
                        <dt class="stat-label flex items-center gap-1.5"><span class="size-2 rounded-full bg-grape"></span> Returning</dt>
                        <dd class="font-display text-2xl tabular-nums mt-1">{{ Format::num($nvr['returning']) }}</dd>
                        <dd class="text-xs text-muted tabular-nums">{{ Format::pct($nvr['returning'] / $nvrTotal * 100) }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        <div class="card card-pad md:col-span-2 xl:col-span-1">
            <h2 class="card-title">Class finder results</h2>
            <p class="text-xs text-muted mt-0.5 mb-4">Which class parents were matched to</p>
            @if ($classFinder->isEmpty())
                <p class="text-sm text-muted py-10 text-center">Nobody used the class finder in this range.</p>
            @else
                <x-admin.analytics.bars :rows="$classFinder->map(fn ($r) => ['label' => $r->label, 'value' => $r->n, 'color' => $r->color, 'sub' => $r->visitors.' '.Str::plural('visitor', $r->visitors)])" />
            @endif
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="card-title">Top pages</h2>
                    <p class="text-xs text-muted mt-0.5">Pageviews in this range</p>
                </div>
                <a href="{{ route('admin.analytics.pages', request()->query()) }}" class="btn btn-ghost btn-sm">All pages <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            @if ($topPages->isEmpty())
                <p class="text-sm text-muted py-10 text-center">No pageviews in this range.</p>
            @else
                <x-admin.analytics.bars color="#E8A317" :rows="$topPages->map(fn ($p) => ['label' => $p->name, 'value' => $p->views, 'sub' => $p->name !== $p->path ? $p->path : null])" />
            @endif
        </div>

        <div class="card card-pad">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="card-title">Top actions</h2>
                    <p class="text-xs text-muted mt-0.5">Taps and clicks parents made (unique visitors in grey)</p>
                </div>
                <a href="{{ route('admin.analytics.actions', request()->query()) }}" class="btn btn-ghost btn-sm">Details <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            @if ($actions->isEmpty())
                <p class="text-sm text-muted py-10 text-center">No actions recorded in this range.</p>
            @else
                <x-admin.analytics.bars color="#7FA82A" :rows="$actions->map(fn ($a) => ['label' => TrackingEvent::NAMES[$a->name] ?? Str::headline($a->name), 'value' => $a->n, 'sub' => '· '.$a->visitors.' '.Str::plural('visitor', $a->visitors)])" />
            @endif
        </div>
    </div>
@endsection
