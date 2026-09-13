@props(['classroom' => null, 'href' => null])
@if ($classroom)
    @php $c = $classroom->color ?: '#8479BD'; @endphp
    @if ($href)
        <a href="{{ $href }}" target="_blank" rel="noopener" {{ $attributes->class('badge hover:underline') }} style="background: {{ $c }}22; color: color-mix(in srgb, {{ $c }} 60%, #26244F)">
            <span class="size-1.5 rounded-full" style="background: {{ $c }}"></span>{{ $classroom->name }}
        </a>
    @else
        <span {{ $attributes->class('badge') }} style="background: {{ $c }}22; color: color-mix(in srgb, {{ $c }} 60%, #26244F)">
            <span class="size-1.5 rounded-full" style="background: {{ $c }}"></span>{{ $classroom->name }}
        </span>
    @endif
@endif
