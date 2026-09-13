@props(['side' => 'right', 'fill' => '#fff'])
{{-- The tail of the logo's speech bubble. Place inside a .mm-bubble; the fill hides the border where it joins. --}}
@if ($side === 'left')
    <svg class="mm-bubble-tail" style="left: 3.25rem" viewBox="0 0 52 32" aria-hidden="true" {{ $attributes }}>
        <path d="M48 1.5C44 13 34 24 4 30c12-9 17-18 18-28.5" fill="{{ $fill }}" stroke="#E8177F" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />
    </svg>
@else
    <svg class="mm-bubble-tail" style="right: 3.25rem" viewBox="0 0 52 32" aria-hidden="true" {{ $attributes }}>
        <path d="M4 1.5C8 13 18 24 48 30 36 21 31 12 30 1.5" fill="{{ $fill }}" stroke="#E8177F" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />
    </svg>
@endif
