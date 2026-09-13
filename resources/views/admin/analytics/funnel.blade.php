@extends('layouts.admin')
@section('title', 'Enrollment funnel')

@php
    use App\Support\Analytics\Format;

    $first = max(1, $steps[0]['count']);
    $leadSteps = ['submitted', 'contacted', 'tour_booked', 'toured', 'enrolled'];
@endphp

@section('content')
    <x-admin.page-header title="Enrollment funnel" subtitle="From a visit on the website to a child enrolled — and where parents drop off." />

    <x-admin.analytics.toolbar :range="$range" :filters="['source', 'device']" />

    @if ($steps[0]['count'] === 0 && $steps[4]['count'] === 0)
        <div class="card"><x-admin.empty icon="funnel" title="No visits in this date range" text="Pick a longer range or clear the filters." /></div>
    @else
        <section class="card card-pad mb-5">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
                <div>
                    <h2 class="card-title">Visits to enrollments</h2>
                    <p class="text-xs text-muted mt-0.5">Website steps count visits (teal); sales steps count website leads created in this range (pink) and their current status.</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-muted">
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-teal"></span> Website</span>
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-brand"></span> Sales</span>
                </div>
            </div>

            <ol class="space-y-4">
                @foreach ($steps as $i => $step)
                    @php
                        $isLead = in_array($step['key'], $leadSteps, true);
                        $ofFirst = Format::ratio($step['count'], $first);
                        $prevStep = $i > 0 ? $steps[$i - 1]['count'] : null;
                        $stepRate = $prevStep ? Format::ratio($step['count'], $prevStep) : null;
                        $dropped = $prevStep !== null ? $prevStep - $step['count'] : null;
                        $prevPeriod = $previous[$step['key']]['count'] ?? null;
                    @endphp
                    <li>
                        @if ($i > 0)
                            <div class="flex items-center gap-2 pl-2 -mt-1 mb-2 text-xs text-muted tabular-nums">
                                <x-icon name="chevron-down" class="size-3.5" />
                                @if ($stepRate !== null)
                                    <span><b class="text-ink">{{ Format::pct($stepRate, 1) }}</b> continued</span>
                                    @if ($dropped > 0)<span>· <b class="text-red-600">{{ Format::num($dropped) }}</b> dropped off</span>@endif
                                @else
                                    <span>—</span>
                                @endif
                            </div>
                        @endif
                        <div class="grid gap-2 sm:grid-cols-[minmax(0,14rem)_1fr] sm:items-center">
                            <div class="min-w-0">
                                <p class="font-bold">{{ $i + 1 }}. {{ $step['label'] }}</p>
                                <p class="text-xs text-muted">{{ $step['hint'] }}</p>
                            </div>
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex-1 h-8 rounded-lg bg-canvas overflow-hidden">
                                    <div class="h-full rounded-lg flex items-center" style="width: {{ max(0.8, min(100, $ofFirst)) }}%; background: {{ $isLead ? '#E8177F' : '#2CBCC9' }}"></div>
                                </div>
                                <div class="w-24 sm:w-28 text-right shrink-0 tabular-nums">
                                    <p class="font-display text-xl leading-none">{{ Format::num($step['count']) }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ Format::pct($ofFirst, $ofFirst < 1 ? 2 : 1) }} of visits</p>
                                </div>
                            </div>
                        </div>
                        @if ($prevPeriod !== null)
                            <p class="text-[11px] text-muted mt-1 sm:pl-[14.5rem] tabular-nums">Previous period: {{ Format::num($prevPeriod) }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
            @php
                $byKey = collect($steps)->keyBy('key');
                $prevByKey = $previous;
                $rate = fn ($a, $b, $src) => Format::ratio($src[$a]['count'] ?? 0, $src[$b]['count'] ?? 0);
            @endphp
            <x-admin.analytics.kpi label="Visit → lead" :value="Format::pct($rate('submitted', 'visits', $byKey), 2)" :current="$rate('submitted', 'visits', $byKey)" :previous="$rate('submitted', 'visits', $prevByKey)" />
            <x-admin.analytics.kpi label="Form start → submit" :value="Format::pct($rate('submitted', 'form_start', $byKey))" :current="$rate('submitted', 'form_start', $byKey)" :previous="$rate('submitted', 'form_start', $prevByKey)" />
            <x-admin.analytics.kpi label="Lead → tour booked" :value="Format::pct($rate('tour_booked', 'submitted', $byKey))" :current="$rate('tour_booked', 'submitted', $byKey)" :previous="$rate('tour_booked', 'submitted', $prevByKey)" />
            <x-admin.analytics.kpi label="Lead → enrolled" :value="Format::pct($rate('enrolled', 'submitted', $byKey))" :current="$rate('enrolled', 'submitted', $byKey)" :previous="$rate('enrolled', 'submitted', $prevByKey)" />
        </div>
        <p class="text-xs text-muted mt-3">Sales steps show each lead's status today, so recent leads may still move further down the funnel.</p>
    @endif
@endsection
