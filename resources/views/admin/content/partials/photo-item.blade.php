{{-- One thumbnail in <x-admin.photos>. Rendered with $photo = null inside the <template> used for new uploads. --}}
@php $p = $photo ?? null; @endphp
<li data-id="{{ $p?->id }}" class="relative overflow-hidden rounded-xl border border-line bg-white">
    <div class="relative aspect-square bg-canvas">
        <img data-photo-img src="{{ $p?->url() }}" alt="{{ $p?->alt }}" loading="lazy" class="absolute inset-0 size-full object-cover">
        <button type="button" data-handle title="Drag to reorder" aria-label="Drag to reorder"
            class="absolute left-2 top-2 grid size-8 cursor-grab place-items-center rounded-lg bg-white/90 text-ink shadow-sm active:cursor-grabbing">
            <x-icon name="grip" class="size-4" />
        </button>
        <div class="absolute right-2 top-2 flex gap-1.5">
            <button type="button" data-feature aria-pressed="{{ $p?->is_featured ? 'true' : 'false' }}" title="Use as main photo" aria-label="Use as main photo"
                class="grid size-8 place-items-center rounded-lg bg-white/90 text-muted shadow-sm hover:text-honey aria-pressed:text-honey aria-pressed:*:fill-current">
                <x-icon name="star" class="size-4" />
            </button>
            <button type="button" data-delete title="Delete photo" aria-label="Delete photo"
                class="grid size-8 place-items-center rounded-lg bg-white/90 text-red-600 shadow-sm hover:bg-red-50">
                <x-icon name="trash" class="size-4" />
            </button>
        </div>
        <span data-featured-badge @unless ($p?->is_featured) hidden @endunless class="badge absolute bottom-2 left-2 bg-honey text-white shadow-sm">Main photo</span>
    </div>
    <input type="text" data-caption value="{{ $p?->caption }}" maxlength="255" placeholder="Add a caption…" aria-label="Caption"
        class="block w-full border-0 border-t border-line bg-white px-2.5 py-2 text-xs text-ink transition-colors placeholder:text-muted/60 focus:bg-canvas/60 focus:outline-none">
</li>
