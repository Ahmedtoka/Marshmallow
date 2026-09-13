@props(['icon' => 'sparkle', 'title', 'text' => null])
<div {{ $attributes->class(['text-center py-14 px-6']) }}>
    <div class="mx-auto size-12 rounded-2xl bg-brand-soft text-brand grid place-items-center mb-3">
        <x-icon :name="$icon" />
    </div>
    <p class="font-display text-lg text-ink">{{ $title }}</p>
    @if ($text) <p class="text-muted mt-1 max-w-sm mx-auto">{{ $text }}</p> @endif
    @if ($slot->isNotEmpty()) <div class="mt-4 flex justify-center gap-2">{{ $slot }}</div> @endif
</div>
