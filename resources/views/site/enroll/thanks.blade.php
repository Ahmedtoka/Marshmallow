@extends('layouts.site')

@section('title', 'Thank you | Marshmallow Nursery')

@push('meta')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <section class="relative overflow-hidden bg-white py-12 sm:py-20">
        <span aria-hidden="true" class="absolute -left-12 top-16 size-40 rounded-full bg-sun/40"></span>
        <span aria-hidden="true" class="absolute right-10 top-10 size-5 rounded-full bg-teal"></span>
        <div class="relative mx-auto max-w-3xl px-5 sm:px-8">
            <div class="mm-bubble mm-rise px-6 pb-9 pt-8 sm:px-12 sm:pb-12 sm:pt-11">
                <span class="grid size-14 place-items-center rounded-full bg-lime-50 text-lime-700"><x-icon name="check" class="size-7" stroke="3" /></span>
                <h1 class="mt-4 font-display text-[2.4rem] font-semibold leading-tight sm:text-5xl">
                    Thank you{{ ! empty($lead['child_name']) ? ', we can’t wait to meet '.$lead['child_name'] : '' }}!
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-ink-soft sm:text-xl">
                    {{ setting('thank_you_message', 'Our admissions team will call you within one working day to answer your questions and book your visit.') }}
                </p>
                @if (! empty($lead['reference']))
                    <p class="mt-4 text-sm font-bold text-ink-muted">Your reference: {{ $lead['reference'] }}</p>
                @endif
                <x-site.bubble-tail side="right" />
            </div>
            <div class="flex justify-end pr-4"><x-site.mascot class="mt-3 w-24 sm:w-28" /></div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @if ($classroom)
                    <a href="{{ route('classes.show', $classroom) }}" class="group flex items-center gap-4 rounded-[1.5rem] border-2 p-5" style="border-color: color-mix(in srgb, {{ $classroom->color }} 40%, #fff); background: color-mix(in srgb, {{ $classroom->color }} 6%, #fff);" data-track="cta_click" data-track-label="Thank you – See {{ $classroom->name }}">
                        <x-site.candy :name="$classroom->icon" :color="$classroom->color" class="size-14" />
                        <span>
                            <span class="block text-sm font-bold text-ink-soft">Your child’s class</span>
                            <span class="block font-display text-2xl font-semibold group-hover:underline" style="color: color-mix(in srgb, {{ $classroom->color }} 72%, #33307A);">{{ $classroom->name }}</span>
                            <span class="block text-sm text-ink-soft">{{ $classroom->ageRangeLabel() }}</span>
                        </span>
                    </a>
                @endif

                @php $contact = $branch ? collect([$branch]) : $branches; @endphp
                <div @class(['rounded-[1.5rem] border-2 border-line-soft p-5', 'sm:col-span-2' => ! $classroom])>
                    <p class="font-bold">Can’t wait for our call?</p>
                    <ul class="mt-3 space-y-3">
                        @foreach ($contact as $b)
                            <li>
                                <p class="text-sm font-bold text-ink-soft">{{ $b->name }}</p>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <a href="{{ $b->telLink() }}" class="btn btn-primary btn-sm" data-track-label="Call {{ $b->name }}"><x-icon name="phone" class="size-4" /> {{ $b->phone }}</a>
                                    <a href="{{ $b->whatsappLink('Hello Marshmallow '.$b->name.', I just sent the enrollment form'.(! empty($lead['reference']) ? ' ('.$lead['reference'].')' : '').'.') }}" target="_blank" rel="noopener" class="btn btn-soft btn-sm" data-track-label="WhatsApp {{ $b->name }}"><x-icon name="whatsapp" class="size-4 text-lime-700" /> WhatsApp</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <p class="mt-8 text-center">
                <a href="{{ route('home') }}" class="link">Back to the homepage</a>
            </p>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', function () {
            var key = 'mm_enroll_tracked_{{ $lead['id'] ?? 'x' }}';
            try { if (sessionStorage.getItem(key)) return; sessionStorage.setItem(key, '1'); } catch (e) {}
            if (window.mmTrack) window.mmTrack('form_submit', 'enroll');
            if (window.gtag) window.gtag('event', 'generate_lead', { form: 'enroll' });
            if (window.fbq) window.fbq('track', 'Lead');
        });
    </script>
@endpush
