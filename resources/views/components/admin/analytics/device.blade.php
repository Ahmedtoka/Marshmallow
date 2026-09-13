@props(['device' => null, 'browser' => null, 'os' => null, 'compact' => false])
@php
    $label = ['mobile' => 'Mobile', 'tablet' => 'Tablet', 'desktop' => 'Desktop'][$device] ?? 'Unknown device';
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 text-muted min-w-0']) }} title="{{ collect([$label, $browser, $os])->filter()->implode(' · ') }}">
    <x-icon :name="$device === 'desktop' ? 'monitor' : 'smartphone'" @class(['shrink-0', 'size-4' => ! $compact, 'size-3.5' => $compact]) />
    <span class="truncate">{{ $compact ? collect([$os, $browser])->filter()->implode(' · ') ?: $label : collect([$label, $browser, $os])->filter()->implode(' · ') }}</span>
</span>
