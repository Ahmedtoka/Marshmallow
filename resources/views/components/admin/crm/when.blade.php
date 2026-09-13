@props(['date' => null, 'empty' => '—', 'short' => false])
@if ($date)
    <time datetime="{{ $date->toIso8601String() }}" title="{{ $date->format('D j M Y, g:i A') }}" {{ $attributes }}>{{ $date->diffForHumans(short: $short) }}</time>
@else
    <span class="text-muted">{{ $empty }}</span>
@endif
