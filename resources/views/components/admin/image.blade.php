@props(['name', 'label' => null, 'path' => null, 'hint' => null, 'aspect' => 'aspect-video', 'fit' => 'object-cover'])
{{-- Image upload with current preview. Sends the file as {name} and a "Remove image" checkbox as remove_{name}. --}}
@php $url = media_url($path); $id = str_replace(['[', ']', '.'], '_', $name); @endphp
<div {{ $attributes->class(['space-y-2']) }} x-data="{ preview: null, remove: false }">
    @if ($label)
        <label class="label mb-0" for="{{ $id }}">{{ $label }}</label>
    @endif
    <div @class(['relative grid w-full place-items-center overflow-hidden rounded-xl border border-line bg-canvas', $aspect, 'input-error' => $errors->has($name)])>
        <template x-if="preview">
            <img :src="preview" alt="" class="absolute inset-0 size-full {{ $fit }}">
        </template>
        @if ($url)
            <img x-show="!preview" src="{{ $url }}" alt="" x-bind:class="remove && 'opacity-30 grayscale'" class="absolute inset-0 size-full {{ $fit }} transition">
            <span x-show="remove && !preview" x-cloak class="badge badge-red relative">Will be removed</span>
        @else
            <div x-show="!preview" class="px-4 text-center text-xs text-muted">
                <x-icon name="image" class="mx-auto mb-1 size-7 text-muted/50" />
                No image yet
            </div>
        @endif
    </div>
    <input type="file" id="{{ $id }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp"
        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null; if (preview) remove = false"
        class="block w-full text-sm text-muted file:mr-3 file:h-9 file:cursor-pointer file:rounded-lg file:border-0 file:bg-canvas file:px-3 file:text-sm file:font-bold file:text-ink hover:file:bg-line">
    @if ($url)
        <label class="flex items-center gap-2 text-sm font-semibold text-muted">
            <input type="checkbox" name="remove_{{ $name }}" value="1" class="checkbox" x-model="remove"> Remove image
        </label>
    @endif
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p class="error">{{ $message }}</p> @enderror
</div>
