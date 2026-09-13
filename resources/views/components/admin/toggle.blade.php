@props(['name', 'label', 'checked' => false, 'hint' => null])
{{-- Always submits a value: 0 when off, 1 when on. Read it with $request->boolean($name). --}}
@php $key = str_replace(['[', ']'], ['.', ''], $name); $on = (bool) old($key, $checked); @endphp
<label {{ $attributes->class(['flex items-start gap-3 cursor-pointer select-none']) }} x-data="{ on: {{ $on ? 'true' : 'false' }} }">
    <input type="hidden" name="{{ $name }}" :value="on ? 1 : 0" value="{{ $on ? 1 : 0 }}">
    <button type="button" role="switch" :aria-checked="on" @click="on = !on"
        :class="on ? 'bg-brand' : 'bg-line'" class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full transition-colors">
        <span :class="on ? 'translate-x-5' : 'translate-x-0.5'" class="mt-0.5 inline-block size-5 rounded-full bg-white shadow transition-transform"></span>
    </button>
    <span>
        <span class="block text-sm font-bold text-ink">{{ $label }}</span>
        @if ($hint) <span class="block text-xs text-muted">{{ $hint }}</span> @endif
    </span>
</label>
