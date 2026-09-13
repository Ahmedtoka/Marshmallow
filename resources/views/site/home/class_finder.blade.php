{{-- Only rendered when the hero is hidden (the hero carries the finder otherwise). --}}
<section class="{{ $tint ? 'bg-blush' : 'bg-white' }} py-16 sm:py-20" id="class-finder">
    <div class="mx-auto grid max-w-5xl items-end gap-6 px-5 sm:px-8 md:grid-cols-[1fr_auto]">
        <div class="mm-bubble p-5 sm:p-8">
            <x-site.class-finder :config="$finderConfig" id="section-finder" place="Class finder section"
                :title="$section->title ?: 'Which class will your child join?'" :subtitle="$section->subtitle" />
            <x-site.bubble-tail side="right" />
        </div>
        <x-site.mascot class="ml-auto w-28 md:w-40" />
    </div>
</section>
