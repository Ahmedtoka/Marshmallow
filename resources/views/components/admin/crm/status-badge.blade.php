@props(['status'])
@php $c = \App\Models\Lead::STATUS_COLORS[$status] ?? '#9B98B8'; @endphp
<span {{ $attributes->class('badge') }} style="background: {{ $c }}22; color: color-mix(in srgb, {{ $c }} 60%, #26244F)">
    <span class="size-1.5 rounded-full" style="background: {{ $c }}"></span>{{ \App\Models\Lead::STATUSES[$status] ?? ucfirst((string) $status) }}
</span>
