{{-- Use inside an element with x-data="lightbox(title)" @keydown.window="keydown($event)" --}}
<template x-teleport="body">
    <div x-show="open" x-cloak x-transition.opacity x-ref="dialog" @keydown="keydown($event)"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-[#1b1947]/95 p-3 sm:p-8"
        role="dialog" aria-modal="true" aria-label="Photo viewer" @click.self="close()">
        <button type="button" x-ref="close" @click="close()" class="absolute right-3 top-3 z-10 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 font-bold text-ink sm:right-6 sm:top-6">
            <x-icon name="x" class="size-5" /> Close
        </button>

        <figure class="flex max-h-full w-full max-w-5xl flex-col items-center">
            <img :src="current.src" :alt="current.alt" class="max-h-[76vh] w-auto max-w-full rounded-[1.25rem] object-contain">
            <figcaption class="mt-3 flex w-full flex-wrap items-center justify-between gap-2 px-1 text-white">
                <span class="font-semibold" x-text="current.caption"></span>
                <span class="text-sm text-white/70" x-show="items.length > 1" x-text="`${index + 1} of ${items.length}`"></span>
            </figcaption>
        </figure>

        <template x-if="items.length > 1">
            <div>
                <button type="button" @click="prev()" class="absolute left-2 top-1/2 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white text-ink sm:left-6">
                    <span class="sr-only">Previous photo</span><x-icon name="arrow-left" class="size-5" />
                </button>
                <button type="button" @click="next()" class="absolute right-2 top-1/2 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white text-ink sm:right-6">
                    <span class="sr-only">Next photo</span><x-icon name="arrow-right" class="size-5" />
                </button>
            </div>
        </template>
    </div>
</template>
