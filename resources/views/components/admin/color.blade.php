@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'default' => '#E8177F'])
{{-- Brand swatches plus a custom hex color. --}}
@php $current = strtoupper((string) (old($name, $value) ?: $default)); $id = str_replace(['[', ']', '.'], '_', $name); @endphp
<div {{ $attributes->class([]) }} x-data="{ color: @js($current) }">
    @if ($label)
        <label class="label" for="{{ $id }}">{{ $label }}</label>
    @endif
    <div class="flex flex-wrap items-center gap-2">
        @foreach (\App\Support\Icons::BRAND_COLORS as $hex => $title)
            <button type="button" title="{{ $title }}" aria-label="{{ $title }}" @click="color = '{{ $hex }}'"
                :class="color.toUpperCase() === '{{ $hex }}' ? 'ring-2 ring-ink ring-offset-2' : 'ring-1 ring-black/5'"
                class="size-8 rounded-full transition" style="background: {{ $hex }}"></button>
        @endforeach
        <span class="ml-1 flex h-10 items-center gap-2 rounded-xl border border-line bg-white pl-1.5 pr-3 focus-within:border-brand">
            <input type="color" :value="/^#[0-9a-f]{6}$/i.test(color) ? color.toLowerCase() : '#000000'" @input="color = $event.target.value.toUpperCase()"
                class="size-7 cursor-pointer rounded-md border-0 bg-transparent p-0" aria-label="Pick a custom color">
            <input type="text" id="{{ $id }}" name="{{ $name }}" x-model="color" maxlength="7" spellcheck="false"
                class="w-20 bg-transparent font-mono text-sm uppercase text-ink outline-none">
        </span>
    </div>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p class="error">{{ $message }}</p> @enderror
</div>
