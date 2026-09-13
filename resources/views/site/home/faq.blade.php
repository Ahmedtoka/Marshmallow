@if ($faqs->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-[0.75fr_1.25fr] lg:gap-16">
            <div class="self-start lg:sticky lg:top-32">
                <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
                <p class="mt-5 max-w-sm leading-relaxed text-ink-soft">Can’t find your answer? Message the team on WhatsApp and a real person will reply.</p>
                @if ($branches->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($branches as $branch)
                            <li>
                                <a href="{{ $branch->whatsappLink('Hello Marshmallow, I have a question.') }}" target="_blank" rel="noopener" class="btn btn-soft btn-sm" data-track-label="WhatsApp {{ $branch->name }} – FAQ">
                                    <x-icon name="whatsapp" class="size-4 text-lime-700" /> {{ $branch->short_name ?: $branch->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @include('site.partials.faq-list', ['faqs' => $faqs])
        </div>
    </section>
@endif
