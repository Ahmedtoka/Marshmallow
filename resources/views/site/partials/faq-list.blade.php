<div x-data="{ open: null }" class="space-y-3">
    @foreach ($faqs as $i => $faq)
        <div class="rounded-[1.25rem] border-2 bg-white transition-colors" :class="open === {{ $i }} ? 'border-pink-200' : 'border-line'">
            <h3>
                <button type="button" id="faq-q-{{ $faq->id }}" aria-controls="faq-a-{{ $faq->id }}"
                    :aria-expanded="(open === {{ $i }}).toString()"
                    @click="open = open === {{ $i }} ? null : {{ $i }}; if (open === {{ $i }} && window.mmTrack) window.mmTrack('faq_open', @js($faq->question))"
                    class="flex w-full items-center justify-between gap-4 rounded-[1.1rem] px-5 py-4 text-left font-display text-[1.1rem] font-medium leading-snug">
                    <span>{{ $faq->question }}</span>
                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-blush text-pink-600 transition-transform" :class="open === {{ $i }} && 'rotate-45'">
                        <x-icon name="plus" class="size-4" />
                    </span>
                </button>
            </h3>
            <div id="faq-a-{{ $faq->id }}" role="region" aria-labelledby="faq-q-{{ $faq->id }}" x-show="open === {{ $i }}" x-cloak>
                <p class="prose-mm px-5 pb-5 text-ink-soft">{{ $faq->answer }}</p>
            </div>
        </div>
    @endforeach
</div>
