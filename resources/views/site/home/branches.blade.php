@if ($branches->isNotEmpty())
    <section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-5 sm:px-8">
            <x-site.section-head :title="$section->title" :subtitle="$section->subtitle" />
            <div class="mt-10 grid gap-5 md:grid-cols-2">
                @foreach ($branches as $i => $branch)
                    @include('site.partials.branch-card', ['branch' => $branch, 'color' => $i % 2 ? '#2CBCC9' : '#E8177F', 'place' => 'Home branches'])
                @endforeach
            </div>
        </div>
    </section>
@endif
