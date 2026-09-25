@extends('layouts.site')

@if (session('meta_event_id'))
    @push('pixel')
        <script>mmPixel('SubmitApplication', @js(\App\Services\MetaConversions::applicationParams()), true, @js(session('meta_event_id')));</script>
    @endpush
@endif

@section('content')
    <div x-data="{ position: @js((string) old('position', '')) }">
        <x-site.page-header title="Grow with Marshmallow" intro="We’re always looking for warm, patient and creative people who love working with young children. Join our teaching team, or start with our internship program from age 15." color="#7FA82A" />

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto grid max-w-6xl items-start gap-10 px-5 sm:px-8 lg:grid-cols-[1.1fr_1fr] lg:gap-14">
                <div>
                    <h2 class="font-display text-[1.85rem] font-semibold leading-tight">Open positions</h2>
                    @if ($openings->isEmpty())
                        <p class="mt-4 rounded-[1.5rem] bg-blush p-5 leading-relaxed text-ink-soft">We have no open positions right now, but we’d still love to hear from you. Send us your CV and we’ll keep it on file.</p>
                    @else
                        <ul class="mt-5 space-y-3">
                            @foreach ($openings as $job)
                                <li>
                                    <details class="group rounded-[1.5rem] border-2 border-line bg-white open:border-pink-200">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-[1.4rem] p-5 [&::-webkit-details-marker]:hidden">
                                            <span>
                                                <span class="block font-display text-xl font-semibold">{{ $job->title }}</span>
                                                <span class="mt-1 flex flex-wrap gap-1.5 text-sm font-bold">
                                                    @if ($job->type)<span class="rounded-full bg-lime-50 px-2.5 py-0.5">{{ $job->type }}</span>@endif
                                                    <span class="rounded-full bg-blush px-2.5 py-0.5">{{ $job->branch?->name ?? 'Both branches' }}</span>
                                                </span>
                                            </span>
                                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-blush text-pink-600 transition-transform group-open:rotate-45"><x-icon name="plus" class="size-4" /></span>
                                        </summary>
                                        <div class="px-5 pb-5">
                                            @if ($job->description)<p class="prose-mm text-ink-soft">{{ $job->description }}</p>@endif
                                            @if (! empty($job->requirements))
                                                <p class="mt-4 font-bold">What we’re looking for</p>
                                                <ul class="mt-2 space-y-1.5">
                                                    @foreach ($job->requirements as $req)
                                                        <li class="flex gap-2.5"><x-icon name="check" class="mt-0.5 size-4 text-lime-700" stroke="3" /> {{ $req }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <a href="#apply" @click="position = @js((string) $job->id)" class="btn btn-outline btn-sm mt-5" data-track="cta_click" data-track-label="Careers – Apply for {{ $job->title }}">Apply for this role</a>
                                        </div>
                                    </details>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div id="apply" class="rounded-[2rem] bg-blush p-5 sm:p-8 lg:sticky lg:top-28">
                    <h2 class="font-display text-[1.7rem] font-semibold leading-tight">Apply now</h2>
                    <p class="mt-1 text-ink-soft">It takes two minutes. Your CV stays private and is only seen by our hiring team.</p>

                    <form method="POST" action="{{ route('careers.apply') }}" enctype="multipart/form-data" data-track-form="careers" novalidate class="mt-5 grid gap-4">
                        @csrf
                        <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                        @if ($errors->any())
                            <p class="rounded-2xl bg-white px-4 py-3 font-bold text-pink-600" role="alert">Please check the highlighted fields.</p>
                        @endif

                        <div>
                            <label for="name" class="field-label">Full name</label>
                            <input id="name" name="name" value="{{ old('name') }}" class="field-input" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')<p id="name-error" class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="field-label">Mobile number</label>
                                <input id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" placeholder="010 1234 5678" class="field-input" autocomplete="tel" required @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                                @error('phone')<p id="phone-error" class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="field-label">Email <span class="font-normal text-ink-muted">(optional)</span></label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="field-input" autocomplete="email" @error('email') aria-invalid="true" @enderror>
                                @error('email')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="position" class="field-label">Position</label>
                                <select id="position" name="position" x-model="position" class="field-input" required @error('position') aria-invalid="true" @enderror>
                                    <option value="">Choose a position</option>
                                    @foreach ($openings as $job)
                                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                                    @endforeach
                                    <option value="other">Other</option>
                                </select>
                                @error('position')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="branch_id" class="field-label">Preferred branch</label>
                                <select id="branch_id" name="branch_id" class="field-input">
                                    <option value="">Either branch</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}" @selected((string) old('branch_id') === (string) $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div x-show="position === 'other'" x-cloak>
                            <label for="position_other" class="field-label">Which role?</label>
                            <input id="position_other" name="position_other" value="{{ old('position_other') }}" class="field-input" @error('position_other') aria-invalid="true" @enderror>
                            @error('position_other')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="message" class="field-label">Tell us about yourself <span class="font-normal text-ink-muted">(optional)</span></label>
                            <textarea id="message" name="message" rows="4" class="field-input" placeholder="Your experience with children, languages you speak, when you can start">{{ old('message') }}</textarea>
                            @error('message')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="cv" class="field-label">CV <span class="font-normal text-ink-muted">(PDF or Word, up to 5 MB)</span></label>
                            <input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                class="field-input py-2.5 file:mr-3 file:rounded-full file:border-0 file:bg-pink-100 file:px-4 file:py-1.5 file:font-bold file:text-pink-600" @error('cv') aria-invalid="true" @enderror>
                            @error('cv')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary mt-1 w-full sm:w-auto sm:justify-self-start sm:px-8" data-track="cta_click" data-track-label="Careers – Send application">Send my application</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
