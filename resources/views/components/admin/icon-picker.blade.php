@props(['name', 'label' => null, 'value' => null, 'hint' => null])
{{-- Grid of the content icons in App\Support\Icons::NAMES. --}}
@php $current = old($name, $value) ?: 'star'; @endphp
<div {{ $attributes->class([]) }} x-data="{ icon: @js($current) }">
    @if ($label)
        <span class="label">{{ $label }} <span class="font-semibold text-muted" x-text="'· ' + icon"></span></span>
    @endif
    <input type="hidden" name="{{ $name }}" :value="icon" value="{{ $current }}">
    <div class="grid max-h-60 grid-cols-6 gap-1.5 overflow-y-auto rounded-xl border border-line bg-white p-2 sm:grid-cols-8">
        @foreach (\App\Support\Icons::NAMES as $icon)
            <button type="button" title="{{ $icon }}" aria-label="{{ $icon }}" @click="icon = '{{ $icon }}'"
                :class="icon === '{{ $icon }}' ? 'bg-brand-soft text-brand ring-2 ring-brand' : 'text-muted hover:bg-canvas hover:text-ink'"
                class="grid aspect-square place-items-center rounded-lg transition-colors">
                <x-icon :name="$icon" />
            </button>
        @endforeach
    </div>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p class="error">{{ $message }}</p> @enderror
</div>
