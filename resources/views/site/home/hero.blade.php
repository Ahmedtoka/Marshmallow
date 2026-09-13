@php
    $finderSection = $sections->get('class_finder');
    $stats = collect([
        setting('years_experience') ? [setting('years_experience'), 'years of happy mornings in Giza', null, 'text-pink-600'] : null,
        setting('recommend_percent') ? [setting('recommend_percent').'%', 'of parents recommend us', setting('reviews_count') ? 'from '.setting('reviews_count').' Facebook reviews' : null, 'text-teal-700'] : null,
        setting('followers') ? [setting('followers'), 'families follow us on Facebook', null, 'text-grape'] : null,
    ])->filter();
@endphp
<section class="relative overflow-hidden bg-white">
    <span aria-hidden="true" class="absolute -left-16 top-24 size-40 rounded-full bg-sun/40 sm:size-56"></span>
    <span aria-hidden="true" class="absolute left-[46%] top-10 hidden size-6 rounded-full bg-teal lg:block"></span>
    <span aria-hidden="true" class="absolute right-8 top-6 size-4 rounded-full bg-grape sm:right-24"></span>
    <span aria-hidden="true" class="absolute bottom-10 left-[38%] hidden size-3 rounded-full bg-pink lg:block"></span>

    <div class="relative mx-auto grid max-w-6xl gap-10 px-5 pb-16 pt-8 sm:px-8 sm:pt-12 lg:grid-cols-[1fr_1.02fr] lg:items-center lg:gap-14 lg:pb-24 lg:pt-16">
        <div class="mm-rise">
            @if (setting('admissions_open') === '1' && setting('admissions_label'))
                <p class="inline-flex items-center gap-2 rounded-full border-2 border-lime bg-lime-50 px-3.5 py-1 text-sm font-bold">
                    <span class="size-2 rounded-full bg-lime-700" aria-hidden="true"></span>
                    {{ setting('admissions_label') }}
                </p>
            @endif

            <h1 class="mt-5 font-display text-[2.55rem] font-semibold leading-[1.03] sm:text-6xl lg:text-[4.1rem]">
                {{ $section->title ?: 'Where little ones learn by playing' }}
            </h1>

            @if ($section->subtitle)
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-ink-soft sm:text-xl">{{ $section->subtitle }}</p>
            @endif
            @if ($section->body)
                <div class="prose-mm mt-4 text-ink-soft">{!! nl2br(e($section->body)) !!}</div>
            @endif

            <div class="mt-7 flex flex-wrap items-center gap-3">
                <a href="{{ url($section->button_url ?: route('enroll')) }}" class="btn btn-primary px-7 text-lg" data-track="cta_click" data-track-label="Hero – {{ $section->button_text ?: 'Book a visit' }}">
                    {{ $section->button_text ?: 'Book a visit' }}
                </a>
                <a href="#hero-finder-dob" class="btn btn-outline lg:hidden" data-track="cta_click" data-track-label="Hero – Find your child’s class">Find your child’s class</a>
            </div>

            @if ($branches->isNotEmpty())
                <ul class="mt-5 flex flex-wrap gap-x-5 gap-y-2">
                    @foreach ($branches as $branch)
                        <li>
                            <a href="{{ $branch->telLink() }}" class="group inline-flex items-center gap-2 font-bold" data-track-label="Call {{ $branch->name }}">
                                <span class="grid size-8 place-items-center rounded-full bg-blush text-pink-600 group-hover:bg-pink-100"><x-icon name="phone" class="size-4" /></span>
                                <span><span class="text-ink-soft">{{ $branch->short_name ?: $branch->name }}</span> {{ $branch->phone }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($stats->isNotEmpty())
                <dl class="mt-9 grid max-w-xl grid-cols-3 gap-3 border-t-2 border-line-soft pt-6">
                    @foreach ($stats as [$value, $label, $note, $color])
                        <div>
                            <dt class="sr-only">{{ $label }}</dt>
                            <dd>
                                <span class="block font-display text-3xl font-semibold leading-none sm:text-4xl {{ $color }}">{{ $value }}</span>
                                <span class="mt-1.5 block text-sm font-bold leading-snug">{{ $label }}</span>
                                @if ($note)
                                    <span class="block text-xs leading-snug text-ink-muted sm:text-sm">{{ $note }}</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>

        <div class="mm-rise mm-rise-delay relative pb-24 sm:pb-28 lg:pb-20" id="class-finder">
            <div class="mm-bubble p-5 sm:p-7">
                <x-site.class-finder
                    :config="$finderConfig"
                    id="hero-finder"
                    place="Hero finder"
                    :title="$finderSection?->title ?: 'Which class will your child join?'"
                    :subtitle="$finderSection?->subtitle" />
                <x-site.bubble-tail side="right" />
            </div>
            <x-site.mascot class="absolute -bottom-2 right-0 w-28 sm:w-32 lg:-right-6 lg:w-36" />
        </div>
    </div>
</section>
