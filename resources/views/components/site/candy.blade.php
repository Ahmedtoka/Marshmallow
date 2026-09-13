@props(['name' => 'cupcake', 'color' => null, 'title' => null])
@php
    $defaults = ['cupcake' => '#E8177F', 'popcorn' => '#E8A317', 'candy' => '#2CBCC9', 'icecream' => '#8479BD', 'lollipop' => '#7FA82A', 'cottoncandy' => '#C0479A'];
    $name = array_key_exists($name, $defaults) ? $name : 'cupcake';
    $c = $color ?: $defaults[$name];

    $mix = function (string $hex, float $amount, string $with = 'ffffff'): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $out = '#';
        for ($i = 0; $i < 3; $i++) {
            $a = hexdec(substr($hex, $i * 2, 2));
            $b = hexdec(substr($with, $i * 2, 2));
            $out .= str_pad(dechex((int) round($a * (1 - $amount) + $b * $amount)), 2, '0', STR_PAD_LEFT);
        }

        return $out;
    };
    $tint = $mix($c, 0.62);
    $soft = $mix($c, 0.82);
    $ink = '#33307A';
    $pink = '#E8177F';
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none" stroke="{{ $ink }}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"
    @if ($title) role="img" aria-label="{{ $title }}" @else aria-hidden="true" @endif
    {{ $attributes->merge(['class' => 'size-14 shrink-0']) }}>
    @switch($name)
        @case('cupcake')
            <path d="M15 34h34l-4.2 21.4A3 3 0 0 1 41.9 58H22.1a3 3 0 0 1-2.9-2.6z" fill="{{ $c }}" />
            <path d="M24.5 34.5 26 57.5M32 34.5v23M39.5 34.5 38 57.5" stroke-width="1.8" stroke="{{ $mix($c, 0.35, '33307a') }}" />
            <path d="M13.5 34c-3.6 0-4.8-6-1-8-1.7-5.8 3.5-9.6 8.6-7.6.8-6.8 9-9.3 13-4.9 4-4 12.3-1.4 11.4 5.2 5.8-1.3 9 5 5.4 8.6 3 2.8 1.2 6.7-2.4 6.7z" fill="{{ $soft }}" />
            <path d="M22 25.5l2-1.5M37 21.5l2.2.8M29 28.5l2.3-.4M43 28l1.3-1.9" stroke="{{ $c }}" stroke-width="2.2" />
            <path d="M33.5 13.5c.4-3 2.4-5.2 5.5-5.8" stroke-width="2" />
            <circle cx="32" cy="12" r="4.6" fill="{{ $pink }}" />
            <circle cx="30.6" cy="10.6" r="1.1" fill="#fff" stroke="none" />
        @break

        @case('popcorn')
            <circle cx="18.5" cy="21" r="6" fill="#FFFBEA" />
            <circle cx="45.5" cy="21" r="6" fill="#FFFBEA" />
            <circle cx="26.5" cy="15.5" r="7" fill="#FFFBEA" />
            <circle cx="37.5" cy="15" r="7" fill="#FFFBEA" />
            <circle cx="32" cy="21.5" r="6" fill="#FFFBEA" />
            <circle cx="24" cy="13" r="1.6" fill="#F6E82B" stroke="none" />
            <circle cx="40" cy="12.5" r="1.6" fill="#F6E82B" stroke="none" />
            <circle cx="47" cy="19" r="1.4" fill="#F6E82B" stroke="none" />
            <path d="M14 27h36l-4.6 29.2A2.2 2.2 0 0 1 43.2 58H20.8a2.2 2.2 0 0 1-2.2-1.8z" fill="#fff" />
            <path d="M20.6 27h6l.6 31h-4.6zM29.3 27h5.4l-.3 31h-4.8zM37.4 27h6l-3.2 31h-4.4z" fill="{{ $c }}" stroke="none" />
            <path d="M14 27h36l-4.6 29.2A2.2 2.2 0 0 1 43.2 58H20.8a2.2 2.2 0 0 1-2.2-1.8z" />
            <rect x="11.5" y="24" width="41" height="6.5" rx="3.2" fill="{{ $c }}" />
        @break

        @case('candy')
            <path d="M18.5 32 5.5 22.2c-2.4 6.4-2.4 13.2 0 19.6z" fill="{{ $tint }}" />
            <path d="M45.5 32l13-9.8c2.4 6.4 2.4 13.2 0 19.6z" fill="{{ $tint }}" />
            <path d="M9 27.5l4 4.5-4 4.5M55 27.5l-4 4.5 4 4.5" stroke="{{ $c }}" stroke-width="1.8" />
            <circle cx="32" cy="32" r="15" fill="{{ $c }}" />
            <path d="M23.5 29c5.2-6.4 11.8-6.4 17 0M23.5 35c5.2 6.4 11.8 6.4 17 0" stroke="#fff" stroke-width="3" />
            <path d="M24 25.5a10.5 10.5 0 0 1 5-4" stroke="#fff" stroke-width="2" opacity=".7" />
        @break

        @case('icecream')
            <path d="M19.5 37h25L33.6 59.4a1.8 1.8 0 0 1-3.2 0z" fill="#F4C25C" />
            <path d="M24 40.5l9.5 12.5M40 40.5l-9.5 12.5M28 37.5l7.5 9.5M36 37.5l-7.5 9.5" stroke-width="1.5" stroke="#B9812A" />
            <path d="M19.5 37h25L33.6 59.4a1.8 1.8 0 0 1-3.2 0z" />
            <path d="M15 36.5c-3.4-11 5.6-17 17-15.6 11.4-1.4 20.4 4.6 17 15.6-2 2.4-4.4 2.4-6 0-1.8 2.6-4.8 2.6-6.6 0-1.8 2.6-5 2.6-6.8 0-1.8 2.6-4.8 2.6-6.6 0-1.6 2.4-4 2.4-6 0z" fill="{{ $c }}" />
            <circle cx="32" cy="15.5" r="10" fill="{{ $tint }}" />
            <path d="M27 12.5l1.8-1.2M35 11l2 .6M31.5 18l1.9-.8M38 17l.8 1.8M25.5 18.5l.8 1.6" stroke="{{ $pink }}" stroke-width="2" />
            <path d="M22 30.5l2-1M41 29.5l1.8 1" stroke="#fff" stroke-width="2.2" />
        @break

        @case('lollipop')
            <rect x="29.8" y="36" width="4.4" height="24" rx="2.2" fill="#fff" />
            <circle cx="32" cy="24" r="18" fill="{{ $c }}" />
            <path d="M32 24a3 3 0 0 1 6 0 6 6 0 0 1-12 0 9 9 0 0 1 18 0 12 12 0 0 1-24 0" stroke="#fff" stroke-width="3" />
            <path d="M18.5 16.5a15 15 0 0 1 6-6" stroke="#fff" stroke-width="2.2" opacity=".75" />
            <path d="M26.5 43.5c2-2 3.5-2.5 5.5-2.5s3.5.5 5.5 2.5c-2 1.6-3.6 2-5.5 2s-3.5-.4-5.5-2z" fill="{{ $pink }}" stroke-width="2" />
        @break

        @case('cottoncandy')
            <path d="M26.5 40.5h11L33.4 60h-2.8z" fill="#fff" />
            <path d="M28.6 45.5h6.8M29.8 51h4.4" stroke="{{ $pink }}" stroke-width="2" />
            <path d="M16.2 35.5c-7.6-.6-7.8-12.4-.2-13.6-1-9 9-13.4 15-8.6 4.2-7 16-5.4 16.4 3.4 8.8.2 11 11.6 3.8 15.6 1.8 7.8-8.8 11.6-13 7.4-4.4 5-14 4-15.4-1.4-3.2 1.6-7.6-.2-6.6-2.8z" fill="{{ $tint }}" />
            <path d="M20 28c3-3.4 7-3.6 9.6-1.2M34.5 21.5c3.4-2.2 7.4-1.2 9 1.8M31 34.5c3 2 7 1.6 9.4-.8M17.5 22.5c1.2-2 3-3 5-3.2" stroke="{{ $c }}" stroke-width="2.2" />
        @break
    @endswitch
</svg>
