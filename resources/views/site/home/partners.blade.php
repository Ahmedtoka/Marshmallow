@if ($partners->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" align="center" />
            @include('site.partials.partners', ['partners' => $partners])
        </div>
    </section>
@endif
