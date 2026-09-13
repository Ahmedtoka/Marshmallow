@extends('layouts.admin')
@section('title', 'Actions')

@php
    use App\Support\Analytics\Format;
    use App\Support\Analytics\Journey;

    $total = fn (array $s) => array_sum($s);
    $hasSeries = $total($series['calls']) + $total($series['whatsapp']) + $total($series['submits']) > 0;

    $chartConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => $series['labels'],
            'datasets' => [
                ['label' => 'Calls', 'data' => $series['calls'], 'backgroundColor' => '#E8A317', 'borderRadius' => 3, 'stack' => 'taps'],
                ['label' => 'WhatsApp', 'data' => $series['whatsapp'], 'backgroundColor' => '#7FA82A', 'borderRadius' => 3, 'stack' => 'taps'],
                ['label' => 'Enrollment form submits', 'data' => $series['submits'], 'type' => 'line', 'borderColor' => '#E8177F', 'backgroundColor' => '#E8177F', 'tension' => 0.3, 'pointRadius' => 2, 'borderWidth' => 2],
            ],
        ],
        'options' => [
            'scales' => [
                'x' => ['stacked' => true, 'grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8, 'maxRotation' => 0]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => ['color' => '#EFEDF7'], 'title' => ['display' => true, 'text' => 'Taps / submits']],
            ],
        ],
    ];

    $drilldowns = [
        'call_click' => ['Calls by branch', 'Which phone number parents tapped', '#E8A317'],
        'whatsapp_click' => ['WhatsApp by branch', 'Which WhatsApp button parents tapped', '#7FA82A'],
        'map_click' => ['Maps opened', 'Directions requested per branch', '#2CBCC9'],
        'cta_click' => ['Buttons clicked', 'Calls-to-action by label', '#8479BD'],
        'class_finder' => ['Class finder results', 'Class each search was matched to', '#E8177F'],
        'faq_open' => ['FAQ questions opened', 'What parents want to know', '#8479BD'],
        'gallery_open' => ['Gallery photos opened', 'By album', '#2CBCC9'],
        'form_start' => ['Forms started', 'By form', '#E8A317'],
        'form_submit' => ['Forms submitted', 'By form', '#E8177F'],
        'video_play' => ['Videos played', 'By video', '#8479BD'],
        'outbound_click' => ['External links', 'Websites parents left to', '#9B98B8'],
        'email_click' => ['Email taps', 'By address', '#9B98B8'],
    ];
    $formLabel = fn ($l) => match ($l) { 'enroll' => 'Enrollment form', 'careers' => 'Careers form', default => $l };
@endphp

@section('content')
    <x-admin.page-header title="Actions" subtitle="Every tap and click that matters: calls, WhatsApp, maps, buttons, class finder and forms." />

    <x-admin.analytics.toolbar :range="$range" :campaigns="$campaigns" />

    <x-admin.analytics.chart class="mb-5" title="Calls, WhatsApp taps and enrollment form submits per {{ $range->isSingleDay() ? 'hour' : 'day' }}"
        subtitle="Calls and WhatsApp are stacked bars; submits are the pink line" :config="$chartConfig" :height="280" :empty="! $hasSeries"
        empty-text="No calls, WhatsApp taps or form submits in this date range." />

    <section class="card overflow-hidden mb-5">
        <div class="px-5 pt-5 pb-3">
            <h2 class="card-title">All actions</h2>
            <p class="text-xs text-muted mt-0.5">Totals, unique visitors and change against the previous period</p>
        </div>
        @if ($actions->isEmpty())
            <x-admin.empty icon="pointer" title="No actions in this date range" />
        @else
            <div class="overflow-x-auto">
                <table class="table tabular-nums">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Unique visitors</th>
                            <th class="text-right">Previous period</th>
                            <th class="text-right">Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($actions as $action)
                            @php
                                $prev = (int) ($previous[$action->name]->n ?? 0);
                                $pct = $prev ? ($action->n - $prev) / $prev * 100 : null;
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">
                                    <span class="inline-flex items-center gap-2 font-bold">
                                        <x-icon :name="Journey::EVENT_ICONS[$action->name] ?? 'pointer'" class="size-4 text-muted" />
                                        {{ $names[$action->name] ?? Str::headline($action->name) }}
                                    </span>
                                </td>
                                <td class="text-right font-bold">{{ Format::num($action->n) }}</td>
                                <td class="text-right">{{ Format::num($action->visitors) }}</td>
                                <td class="text-right text-muted">{{ Format::num($prev) }}</td>
                                <td class="text-right whitespace-nowrap">
                                    @if ($pct === null)
                                        <span class="text-muted text-xs font-bold">New</span>
                                    @elseif (abs($pct) < 0.05)
                                        <span class="text-muted text-xs font-bold">No change</span>
                                    @else
                                        <span @class(['text-xs font-bold', 'text-[#557316]' => $pct > 0, 'text-red-600' => $pct < 0])>{{ $pct > 0 ? '▲' : '▼' }} {{ number_format(abs($pct), 0) }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($labels->isNotEmpty())
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($drilldowns as $name => [$title, $subtitle, $color])
                @continue(! $labels->has($name))
                <section class="card card-pad">
                    <h2 class="card-title">{{ $title }}</h2>
                    <p class="text-xs text-muted mt-0.5 mb-4">{{ $subtitle }} · top {{ $labels[$name]->count() }}</p>
                    <x-admin.analytics.bars :color="$color" :rows="$labels[$name]->map(fn ($r) => [
                        'label' => in_array($name, ['form_start', 'form_submit']) ? $formLabel($r->label) : ($r->label ?: '(no label)'),
                        'value' => $r->n,
                        'sub' => '· '.$r->visitors.' '.Str::plural('visitor', $r->visitors),
                    ])" />
                </section>
            @endforeach
        </div>
    @endif
@endsection
