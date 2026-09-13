@props(['rows' => [], 'color' => '#2CBCC9', 'max' => null, 'unit' => null])
{{--
    Horizontal bar list that stays readable on phones.
    $rows: list of ['label' => ..., 'value' => number, 'display' => ?string, 'sub' => ?string, 'color' => ?string, 'url' => ?string]
--}}
@php
    $rows = collect($rows);
    $max = $max ?? max(1, (float) $rows->max('value'));
@endphp
<ul {{ $attributes->class(['space-y-3']) }}>
    @foreach ($rows as $row)
        @php $width = $max > 0 ? max(1.5, min(100, (float) $row['value'] / $max * 100)) : 0; @endphp
        <li class="min-w-0">
            <div class="flex items-baseline justify-between gap-3 text-sm">
                <span class="min-w-0 truncate font-semibold">
                    @if (! empty($row['url']))
                        <a href="{{ $row['url'] }}" class="hover:text-brand">{{ $row['label'] }}</a>
                    @else
                        {{ $row['label'] }}
                    @endif
                    @if (! empty($row['sub']))<span class="text-xs text-muted font-normal"> {{ $row['sub'] }}</span>@endif
                </span>
                <span class="shrink-0 font-bold tabular-nums">{{ $row['display'] ?? number_format((float) $row['value']) }}{{ $unit }}</span>
            </div>
            <div class="mt-1 h-2 rounded-full bg-canvas overflow-hidden">
                <div class="h-full rounded-full" style="width: {{ $width }}%; background: {{ $row['color'] ?? $color }}"></div>
            </div>
        </li>
    @endforeach
</ul>
