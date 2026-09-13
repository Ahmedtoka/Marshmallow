@extends('layouts.site')

@php
    $interest = old('interest', $prefill['interest']);
    $same = filter_var(old('whatsapp_same', true), FILTER_VALIDATE_BOOLEAN);
    $tourDate = old('tour_date');
@endphp

@section('content')
    <x-site.page-header
        :title="$prefillCamp ? 'Register for '.$prefillCamp->title : ($interest === 'waitlist' ? 'Join the waitlist' : 'Book a visit')"
        intro="Tell us a little about your child. Our admissions team will call you within one working day to answer your questions and arrange your visit.">
        @if ($prefillClass)
            <p class="mt-5 inline-flex items-center gap-2.5 rounded-full bg-white py-1.5 pl-2 pr-4 font-bold">
                <x-site.candy :name="$prefillClass->icon" :color="$prefillClass->color" class="size-8" /> Interested in {{ $prefillClass->name }}, {{ $prefillClass->ageRangeLabel() }}
            </p>
        @endif
    </x-site.page-header>

    <section class="bg-white py-10 sm:py-14">
        <div class="mx-auto grid max-w-6xl items-start gap-10 px-5 sm:px-8 lg:grid-cols-[1.45fr_1fr] lg:gap-14">
            <form method="POST" action="{{ route('enroll.store') }}" data-track-form="enroll" novalidate
                x-data="enrollForm(@js(['interest' => $interest, 'same' => $same, 'tourDate' => $tourDate]))" class="space-y-8">
                @csrf
                <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                @if ($errors->any())
                    <div class="flex gap-3 rounded-[1.25rem] border-2 border-pink-200 bg-blush p-4" role="alert">
                        <x-icon name="alert" class="mt-0.5 size-5 text-pink-600" />
                        <div>
                            <p class="font-bold">A few details need another look</p>
                            <ul class="mt-1 list-disc pl-5 text-sm text-ink-soft">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- About you --}}
                <fieldset class="space-y-4">
                    <legend class="font-display text-2xl font-semibold">About you</legend>
                    <div>
                        <label for="parent_name" class="field-label">Your name</label>
                        <input id="parent_name" name="parent_name" value="{{ old('parent_name') }}" class="field-input" autocomplete="name" required @error('parent_name') aria-invalid="true" aria-describedby="parent_name-error" @enderror>
                        @error('parent_name')<p id="parent_name-error" class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="field-label">Mobile number</label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" placeholder="010 1234 5678" class="field-input" autocomplete="tel" required aria-describedby="phone-hint @error('phone') phone-error @enderror" @error('phone') aria-invalid="true" @enderror>
                            <p id="phone-hint" class="field-hint">We’ll call you on this number.</p>
                            @error('phone')<p id="phone-error" class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="field-label">Email <span class="font-normal text-ink-muted">(optional)</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="field-input" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="whatsapp_same" value="0">
                        <label class="flex cursor-pointer items-center gap-3 font-bold">
                            <input type="checkbox" name="whatsapp_same" value="1" x-model="same" class="size-5 rounded accent-pink">
                            WhatsApp is the same number
                        </label>
                        <div x-show="!same" x-cloak class="mt-3">
                            <label for="whatsapp" class="field-label">WhatsApp number</label>
                            <input id="whatsapp" name="whatsapp" type="tel" inputmode="tel" value="{{ old('whatsapp') }}" placeholder="010 1234 5678" class="field-input sm:max-w-xs" :disabled="same" @error('whatsapp') aria-invalid="true" @enderror>
                            @error('whatsapp')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                {{-- About your child --}}
                <fieldset class="space-y-4 rounded-[1.75rem] bg-blush p-5 sm:p-6"
                    x-data="classFinder(@js($finderConfig + ['enrollUrl' => route('enroll'), 'campsUrl' => route('camps.index')]), @js(['dob' => old('child_dob', $prefill['child_dob']), 'year' => old('academic_year', $prefill['academic_year']), 'track' => false]))">
                    <legend class="float-left mb-4 w-full font-display text-2xl font-semibold">About your child</legend>
                    <div class="clear-both grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="child_name" class="field-label">Child’s name <span class="font-normal text-ink-muted">(optional)</span></label>
                            <input id="child_name" name="child_name" value="{{ old('child_name') }}" class="field-input" autocomplete="off" @error('child_name') aria-invalid="true" @enderror>
                            @error('child_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="child_dob" class="field-label">Birthday</label>
                            <input id="child_dob" name="child_dob" type="date" x-model="dob" :max="maxDate" @change="compute()" @blur="compute()" class="field-input" @error('child_dob') aria-invalid="true" aria-describedby="child_dob-error" @enderror>
                            @error('child_dob')<p id="child_dob-error" class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="academic_year" class="field-label">Joining in school year</label>
                            <select id="academic_year" name="academic_year" x-model="year" @change="compute()" class="field-input" @error('academic_year') aria-invalid="true" @enderror>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ str_replace('-', ' – ', $year) }}</option>
                                @endforeach
                            </select>
                            @error('academic_year')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div aria-live="polite">
                        <p x-show="error" x-text="error" class="field-error" x-cloak></p>
                        <template x-if="result && result.status === 'match'">
                            <div class="flex items-center gap-3 rounded-2xl border-2 bg-white p-3" :style="`border-color: ${result.classroom.color}`">
                                @foreach ($finderConfig['classes'] as $c)
                                    <span x-show="result.classroom.slug === @js($c['slug'])"><x-site.candy :name="$c['icon']" :color="$c['color']" class="size-11" /></span>
                                @endforeach
                                <p class="leading-snug">
                                    <span class="block font-display text-lg font-semibold" :style="`color: color-mix(in srgb, ${result.classroom.color} 75%, #33307A)`" x-text="`${result.classroom.name} class`"></span>
                                    <span class="text-sm text-ink-soft"><span x-text="result.isToday ? 'Your child is' : 'Your child will be'"></span> <span x-text="result.age"></span> <span x-text="result.on"></span>.</span>
                                </p>
                            </div>
                        </template>
                        <template x-if="result && result.status === 'too_young'">
                            <p class="rounded-2xl border-2 border-teal bg-white p-3 text-sm leading-relaxed"><strong>Not old enough yet.</strong> We’ll add you to the waitlist and call you when a place fits.</p>
                        </template>
                        <template x-if="result && result.status === 'too_old'">
                            <p class="rounded-2xl border-2 border-grape bg-white p-3 text-sm leading-relaxed"><strong>Past nursery age.</strong> Choose “A holiday camp” below, or ask us a question.</p>
                        </template>
                    </div>
                </fieldset>

                {{-- Your visit --}}
                <fieldset class="space-y-4">
                    <legend class="font-display text-2xl font-semibold">Your visit</legend>

                    <div role="radiogroup" aria-labelledby="branch-label">
                        <p id="branch-label" class="field-label">Branch</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($branches as $b)
                                <label class="relative flex cursor-pointer gap-3 rounded-[1.25rem] border-2 border-line bg-white p-4 has-[:checked]:border-pink has-[:checked]:bg-blush has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-teal">
                                    <input type="radio" name="branch_id" value="{{ $b->id }}" class="mt-1 size-4 accent-pink" @checked((string) old('branch_id', $prefill['branch_id']) === (string) $b->id) required>
                                    <span>
                                        <span class="block font-display text-lg font-semibold">{{ $b->name }}</span>
                                        <span class="block text-sm text-ink-soft">{{ $b->address_note ?: \Illuminate\Support\Str::limit($b->address, 48) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('branch_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="interest" class="field-label">I’m interested in</label>
                            <select id="interest" name="interest" x-model="interest" class="field-input" @error('interest') aria-invalid="true" @enderror>
                                @if ($interest === 'waitlist')
                                    <option value="waitlist">Joining the waitlist</option>
                                @endif
                                @foreach (\App\Http\Requests\Site\EnrollRequest::INTERESTS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('interest')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="interest === 'camp'" x-cloak>
                            <label for="camp_id" class="field-label">Which camp?</label>
                            <select id="camp_id" name="camp_id" class="field-input" :disabled="interest !== 'camp'" @error('camp_id') aria-invalid="true" @enderror>
                                <option value="">Choose a camp</option>
                                @foreach ($camps as $camp)
                                    <option value="{{ $camp->id }}" @selected((string) old('camp_id', $prefill['camp_id']) === (string) $camp->id)>{{ $camp->title }}</option>
                                @endforeach
                            </select>
                            @error('camp_id')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border-2 border-line-soft p-4 sm:p-5">
                        <p class="font-bold">Preferred visit time <span class="font-normal text-ink-muted">(optional)</span></p>
                        <p class="field-hint mt-0.5">Sunday to Thursday. We’ll confirm the exact time when we call.</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="tour_date" class="field-label">Day</label>
                                <input id="tour_date" name="tour_date" type="date" x-model="tourDate" :min="minTour" class="field-input" @error('tour_date') aria-invalid="true" aria-describedby="tour_date-error" @enderror>
                                @error('tour_date')
                                    <p id="tour_date-error" class="field-error">{{ $message }}</p>
                                @else
                                    <p x-show="tourWeekend" x-cloak class="field-error">We’re closed on Friday and Saturday. Please pick a day from Sunday to Thursday.</p>
                                @enderror
                            </div>
                            <div>
                                <label for="tour_time" class="field-label">Time</label>
                                <select id="tour_time" name="tour_time" class="field-input" @error('tour_time') aria-invalid="true" @enderror>
                                    <option value="">Any time</option>
                                    @foreach (\App\Http\Requests\Site\EnrollRequest::TOUR_SLOTS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('tour_time') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('tour_time')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Anything else --}}
                <fieldset class="space-y-4">
                    <legend class="font-display text-2xl font-semibold">Anything else</legend>
                    <div>
                        <label for="heard_from" class="field-label">How did you hear about us? <span class="font-normal text-ink-muted">(optional)</span></label>
                        <select id="heard_from" name="heard_from" class="field-input sm:max-w-xs">
                            <option value="">Choose one</option>
                            @foreach (\App\Http\Requests\Site\EnrollRequest::HEARD_FROM as $option)
                                <option value="{{ $option }}" @selected(old('heard_from') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="message" class="field-label">Questions or notes <span class="font-normal text-ink-muted">(optional)</span></label>
                        <textarea id="message" name="message" rows="4" class="field-input" placeholder="Allergies, transport, anything you’d like us to know">{{ old('message') }}</textarea>
                        @error('message')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </fieldset>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-5">
                    <button type="submit" class="btn btn-primary px-9 text-lg" data-track="cta_click" data-track-label="Enroll form – Send">Send</button>
                    <p class="text-sm text-ink-soft">We only use your details to contact you about Marshmallow.</p>
                </div>
            </form>

            <aside class="space-y-5 lg:sticky lg:top-28">
                <div class="mm-bubble p-6 sm:p-7">
                    <h2 class="font-display text-xl font-semibold">What happens next</h2>
                    <ol class="mt-4 space-y-4">
                        @foreach ([
                            ['We call you', 'Within one working day, from the branch you chose.'],
                            ['You visit', setting('tour_hours') ?: 'See the classrooms, garden and team.'],
                            ['Your child settles in', 'We plan a gentle first week together.'],
                        ] as $i => [$step, $note])
                            <li class="flex gap-3">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-pink font-display font-semibold text-white">{{ $i + 1 }}</span>
                                <span><span class="block font-bold">{{ $step }}</span><span class="block text-sm text-ink-soft">{{ $note }}</span></span>
                            </li>
                        @endforeach
                    </ol>
                    <x-site.bubble-tail side="right" />
                </div>
                <div class="flex justify-end pr-3"><x-site.mascot class="w-20" /></div>
                <div class="rounded-[1.5rem] border-2 border-line-soft p-5">
                    <p class="font-bold">Prefer to talk now?</p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($branches as $b)
                            <li class="flex flex-wrap items-center justify-between gap-2">
                                <a href="{{ $b->telLink() }}" class="inline-flex items-center gap-2 font-bold hover:underline" data-track-label="Call {{ $b->name }}"><x-icon name="phone" class="size-4 text-pink" /> {{ $b->short_name ?: $b->name }} {{ $b->phone }}</a>
                                <a href="{{ $b->whatsappLink('Hello Marshmallow '.$b->name.', I would like to book a visit.') }}" target="_blank" rel="noopener" class="text-sm font-bold text-lime-700 hover:underline" data-track-label="WhatsApp {{ $b->name }}">WhatsApp</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </section>
@endsection
