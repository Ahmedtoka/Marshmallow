@props(['badges' => []])
{{-- Key-behaviour badges from App\Support\Analytics\Journey::badges(). --}}
@if ($badges)
    <div {{ $attributes->class(['flex flex-wrap gap-1.5']) }}>
        @foreach ($badges as $badge)
            @if (! empty($badge['url']))
                <a href="{{ $badge['url'] }}" class="badge {{ $badge['class'] }} hover:brightness-95"><x-icon :name="$badge['icon']" class="size-3" /> {{ $badge['label'] }}</a>
            @else
                <span class="badge {{ $badge['class'] }}"><x-icon :name="$badge['icon']" class="size-3" /> {{ $badge['label'] }}</span>
            @endif
        @endforeach
    </div>
@endif
