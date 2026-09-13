@props(['title', 'subtitle' => null, 'config' => [], 'height' => 260, 'empty' => false, 'emptyText' => 'No data in this date range.'])
{{--
    Chart.js card. $config is a plain Chart.js config array (type, data, options).
    The canvas sits in a fixed-height relative box with maintainAspectRatio off, so it never overflows on phones.
--}}
@php $chartId = 'chart-'.\Illuminate\Support\Str::random(8); @endphp
<div {{ $attributes->class(['card card-pad min-w-0']) }}>
    <div class="mb-3">
        <h2 class="card-title">{{ $title }}</h2>
        @if ($subtitle)<p class="text-xs text-muted mt-0.5">{{ $subtitle }}</p>@endif
    </div>
    @if ($empty)
        <div class="grid place-items-center text-sm text-muted rounded-xl bg-canvas/60" style="height: {{ $height }}px">{{ $emptyText }}</div>
    @else
        <div class="relative w-full" style="height: {{ $height }}px">
            <canvas id="{{ $chartId }}" role="img" aria-label="{{ $title }}"></canvas>
        </div>
        {{ $slot }}
    @endif
</div>

@unless ($empty)
    @once
        @push('scripts')
            <script>
                window.mmCharts = window.mmCharts || [];
                document.addEventListener('DOMContentLoaded', () => {
                    window.mmCharts.forEach(([id, config]) => {
                        const el = document.getElementById(id);
                        if (!el || !window.Chart) return;
                        config.options = Object.assign({ responsive: true, maintainAspectRatio: false }, config.options || {});
                        config.options.plugins = Object.assign({ legend: { position: 'bottom', labels: { boxWidth: 8, boxHeight: 8, padding: 14 } } }, config.options.plugins || {});
                        config.options.plugins.tooltip = Object.assign({ mode: 'index', intersect: false }, config.options.plugins.tooltip || {});
                        new window.Chart(el, config);
                    });
                });
            </script>
        @endpush
    @endonce
    @push('scripts')
        <script>(window.mmCharts = window.mmCharts || []).push([@js($chartId), @json($config)]);</script>
    @endpush
@endunless
