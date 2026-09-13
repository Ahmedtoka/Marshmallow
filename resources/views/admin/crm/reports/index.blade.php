@extends('layouts.admin')
@section('title', 'Sales reports')
@section('content')
    <x-admin.page-header title="Sales reports" :subtitle="'Leads created '.$from->format('j M Y').' – '.$to->format('j M Y')" />

    <form method="GET" class="card p-3 mb-5 flex flex-wrap items-center gap-2" x-data="{ range: @js($preset) }">
        <div class="flex flex-wrap gap-1">
            @foreach (\App\Http\Controllers\Admin\Crm\ReportController::PRESETS as $k => $label)
                <label :class="range === @js($k) ? 'bg-ink text-white' : 'bg-canvas text-muted hover:text-ink'" class="cursor-pointer rounded-lg px-3 h-9 inline-flex items-center text-sm font-bold">
                    <input type="radio" name="range" value="{{ $k }}" class="sr-only" x-model="range" @if ($k !== 'custom') @change="$el.form.submit()" @endif> {{ $label }}
                </label>
            @endforeach
        </div>
        <div x-show="range === 'custom'" x-cloak class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="input h-9 w-auto" aria-label="From">
            <span class="text-muted">to</span>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="input h-9 w-auto" aria-label="To">
            <button class="btn btn-primary btn-sm h-9">Apply</button>
        </div>
    </form>

    @php
        $tiles = [
            ['New leads', $kpis['leads'], null],
            ['Contacted', $kpis['contacted_pct'] !== null ? $kpis['contacted_pct'].'%' : '—', 'Moved past "new" or had a call / WhatsApp'],
            ['Tours booked', $kpis['tours'], null],
            ['Enrolled', $kpis['enrolled'], null],
            ['Conversion', $kpis['conversion_pct'] !== null ? $kpis['conversion_pct'].'%' : '—', 'Enrolled ÷ leads'],
            ['First response', $kpis['median_response'] ?? '—', 'Median time to first contact'],
            ['Overdue follow-ups', $kpis['overdue_now'], 'Right now, whole team'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-3 mb-5">
        @foreach ($tiles as [$label, $value, $hint])
            <div class="card p-4" @if ($hint) title="{{ $hint }}" @endif>
                <div class="stat-label">{{ $label }}</div>
                <div @class(['stat-value mt-2', 'text-red-600' => $label === 'Overdue follow-ups' && $value > 0])>{{ $value }}</div>
                @if ($hint)<div class="text-[11px] text-muted mt-1.5 leading-tight">{{ $hint }}</div>@endif
            </div>
        @endforeach
    </div>

    @if ($total === 0)
        <div class="card">
            <x-admin.empty icon="trending" title="No leads in this period" text="Pick a wider date range to see charts and team performance." />
        </div>
    @else
        <div class="grid gap-5 lg:grid-cols-2 mb-5">
            <section class="card card-pad lg:col-span-2">
                <h2 class="card-title">Leads per week</h2>
                <p class="text-xs text-muted mb-3">Coloured by where each lead is now.</p>
                <div class="h-72"><canvas id="chart-weekly"></canvas></div>
            </section>
            <section class="card card-pad">
                <h2 class="card-title mb-3">By source</h2>
                <div class="h-72"><canvas id="chart-source"></canvas></div>
            </section>
            <section class="card card-pad">
                <h2 class="card-title mb-3">By branch</h2>
                <div class="h-72"><canvas id="chart-branch"></canvas></div>
            </section>
            <section class="card card-pad lg:col-span-2">
                <h2 class="card-title mb-3">By class</h2>
                <div class="h-64"><canvas id="chart-class"></canvas></div>
            </section>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <section class="card overflow-hidden">
            <div class="card-pad pb-3">
                <h2 class="card-title">Team performance</h2>
                <p class="text-xs text-muted">Leads created in this period, by who they are assigned to.@if ($unassigned) {{ $unassigned }} still unassigned.@endif</p>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Agent</th><th class="text-right">Assigned</th><th class="text-right">Contacted</th><th class="text-right">Tours</th>
                        <th class="text-right">Enrolled</th><th class="text-right">Conversion</th><th class="text-right">Overdue</th><th class="text-right">Avg. response</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($agents as $row)
                        <tr>
                            <td><div class="flex items-center gap-2 whitespace-nowrap"><x-admin.crm.avatar :user="$row['user']" /> <span class="font-bold">{{ $row['user']->name }}</span></div></td>
                            <td class="text-right">{{ $row['assigned'] }}</td>
                            <td class="text-right">{{ $row['contacted'] }}</td>
                            <td class="text-right">{{ $row['tours'] }}</td>
                            <td class="text-right font-bold">{{ $row['enrolled'] }}</td>
                            <td class="text-right">{{ $row['conversion'] !== null ? $row['conversion'].'%' : '—' }}</td>
                            <td @class(['text-right', 'text-red-600 font-bold' => $row['overdue'] > 0])>{{ $row['overdue'] }}</td>
                            <td class="text-right whitespace-nowrap">{{ $row['avg_response'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-8">No sales agents yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="card-title mb-3">Why families said no</h2>
            @if ($lostReasons->isEmpty())
                <p class="text-sm text-muted">No lost leads in this period.</p>
            @else
                @php $maxLost = $lostReasons->max(); @endphp
                <ul class="space-y-3">
                    @foreach ($lostReasons as $reason => $count)
                        <li>
                            <div class="flex justify-between text-sm"><span class="font-semibold">{{ $reason }}</span><span class="font-bold">{{ $count }}</span></div>
                            <div class="mt-1 h-2 rounded-full bg-canvas overflow-hidden"><div class="h-full rounded-full bg-[#9B98B8]" style="width: {{ round($count / $maxLost * 100) }}%"></div></div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection

@if ($total > 0)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const charts = @js($charts);
        const grid = { color: '#EFEDF7' };
        const common = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };

        new Chart(document.getElementById('chart-weekly'), {
            type: 'bar',
            data: { labels: charts.weekly.labels, datasets: charts.weekly.datasets.map((d) => ({ ...d, borderRadius: 4, maxBarThickness: 48 })) },
            options: { ...common, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid } } },
        });

        const pair = (id, data, horizontal) => new Chart(document.getElementById(id), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Leads', data: data.leads, backgroundColor: '#8479BD', borderRadius: 4, maxBarThickness: 28 },
                    { label: 'Enrolled', data: data.enrolled, backgroundColor: '#7FA82A', borderRadius: 4, maxBarThickness: 28 },
                ],
            },
            options: { ...common, indexAxis: horizontal ? 'y' : 'x', scales: { x: { beginAtZero: true, ticks: { precision: 0 }, grid: horizontal ? grid : { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: horizontal ? { display: false } : grid } } },
        });
        pair('chart-source', charts.source, true);
        pair('chart-branch', charts.branch, false);

        new Chart(document.getElementById('chart-class'), {
            type: 'bar',
            data: {
                labels: charts.class.labels,
                datasets: [
                    { label: 'Leads', data: charts.class.leads, backgroundColor: charts.class.colors, borderRadius: 4, maxBarThickness: 48 },
                    { label: 'Enrolled', data: charts.class.enrolled, backgroundColor: '#26244F', borderRadius: 4, maxBarThickness: 48 },
                ],
            },
            options: { ...common, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid } } },
        });
    });
</script>
@endpush
@endif
