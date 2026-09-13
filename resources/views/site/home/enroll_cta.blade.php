<section class="bg-white px-4 py-12 sm:px-8 sm:py-16">
    @include('site.partials.enroll-band', [
        'title' => $section->title ?: 'Come and see us',
        'subtitle' => $section->subtitle,
        'buttonText' => $section->button_text ?: 'Book a visit',
        'buttonUrl' => url($section->button_url ?: route('enroll')),
        'place' => 'Home CTA',
    ])
</section>
