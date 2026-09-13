@if ($testimonials->isNotEmpty())
    @php
        $featured = $testimonials->first();
        $rest = $testimonials->slice(1)->take(4);
    @endphp
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
            <div>
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                @if (setting('recommend_percent'))
                    <div class="mt-8 flex items-end gap-4">
                        <p class="font-display text-7xl font-semibold leading-none text-pink sm:text-8xl">{{ setting('recommend_percent') }}%</p>
                        <p class="pb-2 font-bold leading-snug">of parents<br>recommend us</p>
                    </div>
                    @if (setting('reviews_count'))
                        <p class="mt-3 text-ink-soft">Based on {{ setting('reviews_count') }} reviews from Marshmallow families on Facebook.</p>
                    @endif
                @endif
                @if (setting('facebook_url'))
                    <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" class="link mt-4 inline-flex items-center gap-1.5">
                        <x-icon name="facebook" class="size-4" /> Read reviews on Facebook
                    </a>
                @endif
            </div>

            <div>
                <figure>
                    <blockquote class="mm-bubble px-6 pb-7 pt-6 sm:px-9 sm:pb-9 sm:pt-8">
                        <svg viewBox="0 0 40 30" class="h-7 w-9 text-sun" fill="currentColor" aria-hidden="true"><path d="M0 30V17C0 7 5 1 15 0l1 5c-5 2-7 5-7 10h7v15zm23 0V17c0-10 5-16 15-17l1 5c-5 2-7 5-7 10h7v15z" /></svg>
                        <p class="mt-3 font-display text-xl font-medium leading-relaxed sm:text-2xl">{{ $featured->quote }}</p>
                        <x-site.bubble-tail side="left" />
                    </blockquote>
                    <figcaption class="mt-9 pl-6 sm:pl-9">
                        <span class="block font-bold">{{ $featured->parent_name }}</span>
                        @if ($featured->relation)
                            <span class="block text-sm text-ink-soft">{{ $featured->relation }}</span>
                        @endif
                    </figcaption>
                </figure>

                @if ($rest->isNotEmpty())
                    <div class="mt-10 grid gap-6 sm:grid-cols-2">
                        @foreach ($rest as $t)
                            <figure class="border-l-4 border-pink-200 pl-5">
                                <blockquote class="leading-relaxed">{{ $t->quote }}</blockquote>
                                <figcaption class="mt-3 text-sm">
                                    <span class="font-bold">{{ $t->parent_name }}</span>
                                    @if ($t->relation)
                                        <span class="block text-ink-soft">{{ $t->relation }}</span>
                                    @endif
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
