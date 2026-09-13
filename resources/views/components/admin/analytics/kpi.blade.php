@props(['label', 'value', 'current' => null, 'previous' => null, 'invert' => false, 'hint' => null, 'color' => null])
{{-- KPI tile with a ▲/▼ change against the previous period. $invert: lower is better (e.g. bounce rate). --}}
@php
    $delta = null;
    if ($current !== null && $previous !== null) {
        if ((float) $previous == 0.0) {
            $delta = (float) $current > 0 ? ['text' => 'New this period', 'tone' => 'muted'] : ['text' => 'No change', 'tone' => 'muted'];
        } else {
            $pct = ((float) $current - (float) $previous) / (float) $previous * 100;
            if (abs($pct) < 0.05) {
                $delta = ['text' => 'No change', 'tone' => 'muted'];
            } else {
                $up = $pct > 0;
                $good = $invert ? ! $up : $up;
                $delta = [
                    'text' => ($up ? '▲ ' : '▼ ').number_format(abs($pct), abs($pct) >= 10 ? 0 : 1).'%',
                    'tone' => $good ? 'good' : 'bad',
                    'sr' => ($up ? 'Up ' : 'Down ').number_format(abs($pct), 1).'% vs previous period',
                ];
            }
        }
    }
@endphp
<div {{ $attributes->class(['card p-4 flex flex-col gap-2 min-w-0']) }}>
    <div class="stat-label flex items-center gap-1.5 min-w-0">
        @if ($color)<span class="size-2 rounded-full shrink-0" style="background: {{ $color }}"></span>@endif
        <span class="truncate">{{ $label }}</span>
    </div>
    <div class="stat-value tabular-nums truncate">{{ $value }}</div>
    @if ($delta)
        <div @class([
            'text-xs font-bold tabular-nums',
            'text-[#557316]' => $delta['tone'] === 'good',
            'text-red-600' => $delta['tone'] === 'bad',
            'text-muted' => $delta['tone'] === 'muted',
        ])>
            <span aria-hidden="true">{{ $delta['text'] }}</span>
            @isset($delta['sr'])<span class="sr-only">{{ $delta['sr'] }}</span>@endisset
            <span class="font-semibold text-muted" aria-hidden="true">vs prev.</span>
        </div>
    @elseif ($hint)
        <div class="text-xs text-muted">{{ $hint }}</div>
    @endif
</div>
