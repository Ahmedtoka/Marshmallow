{{-- Sticky save bar for long forms. Optional: $label, $cancel (url), $note. --}}
<div class="sticky bottom-3 z-10 mt-6 rounded-2xl border border-line bg-white/95 px-4 py-3 shadow-lg shadow-ink/5 backdrop-blur">
    <div class="flex items-center justify-end gap-2">
        @if (! empty($note))
            <p class="mr-auto hidden text-xs text-muted sm:block">{{ $note }}</p>
        @endif
        @if (! empty($cancel))
            <a href="{{ $cancel }}" class="btn btn-ghost">Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary">
            <x-icon name="check" class="size-4" /> {{ $label ?? 'Save changes' }}
        </button>
    </div>
</div>
