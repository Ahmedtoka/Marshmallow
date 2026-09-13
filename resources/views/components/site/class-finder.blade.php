@props(['config', 'title' => null, 'subtitle' => null, 'id' => 'finder', 'place' => 'Class finder'])
@php
    $config = $config + ['enrollUrl' => route('enroll'), 'campsUrl' => route('camps.index')];
    $classes = collect($config['classes']);
    $youngest = $classes->sortBy('min')->first();
@endphp
<div x-data="classFinder(@js($config))" {{ $attributes }}>
    @if ($title)
        <h2 class="font-display text-[1.6rem] font-semibold leading-tight sm:text-[1.9rem]">{{ $title }}</h2>
    @endif
    @if ($subtitle)
        <p class="mt-2 leading-relaxed text-ink-soft">{{ $subtitle }}</p>
    @endif

    <div class="mt-5 grid gap-3 sm:grid-cols-[1.2fr_1fr]">
        <div>
            <label for="{{ $id }}-dob" class="field-label">Your child’s birthday</label>
            <input id="{{ $id }}-dob" type="date" class="field-input" x-model="dob" :max="maxDate" @change="compute()" @blur="compute()" autocomplete="off">
        </div>
        <div>
            <label for="{{ $id }}-year" class="field-label">Joining in school year</label>
            <select id="{{ $id }}-year" class="field-input" x-model="year" @change="compute()">
                @foreach ($config['years'] as $year)
                    <option value="{{ $year }}">{{ str_replace('-', ' – ', $year) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-5" aria-live="polite">
        <p x-show="error" x-text="error" class="field-error" x-cloak></p>

        {{-- Before a birthday is picked: the six classes waiting --}}
        <div x-show="!result && !error" class="flex items-center gap-3 rounded-2xl bg-blush px-4 py-3">
            <div class="flex -space-x-2">
                @foreach ($classes as $c)
                    <span class="grid size-9 place-items-center rounded-full bg-white ring-2 ring-white">
                        <x-site.candy :name="$c['icon']" :color="$c['color']" class="size-7" />
                    </span>
                @endforeach
            </div>
            <p class="text-sm font-semibold text-ink-soft">Pick a birthday to see which class fits.</p>
        </div>

        <template x-if="result && result.status === 'match'">
            <div class="rounded-[1.4rem] border-2 p-4 sm:p-5" :style="`border-color: ${result.classroom.color}; background: color-mix(in srgb, ${result.classroom.color} 7%, #fff)`">
                <div class="flex items-start gap-4">
                    <div class="grid size-16 shrink-0 place-items-center rounded-2xl bg-white sm:size-[4.5rem]">
                        @foreach ($classes as $c)
                            <span x-show="result.classroom.slug === @js($c['slug'])">
                                <x-site.candy :name="$c['icon']" :color="$c['color']" class="size-12 sm:size-14" />
                            </span>
                        @endforeach
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-ink-soft">Your child’s class</p>
                        <p class="font-display text-[1.7rem] font-semibold leading-tight" :style="`color: color-mix(in srgb, ${result.classroom.color} 78%, #33307A)`" x-text="result.classroom.name"></p>
                        <p class="text-sm font-bold text-ink-soft" x-text="result.classroom.age"></p>
                    </div>
                </div>
                <p class="mt-3 italic text-ink-soft" x-show="result.classroom.tagline" x-text="result.classroom.tagline"></p>
                <p class="mt-2 font-semibold">
                    <span x-text="result.isToday ? 'Your child is' : 'Your child will be'"></span>
                    <strong class="text-pink-600" x-text="result.age"></strong>
                    <span x-text="result.on"></span>.
                </p>
                <div class="mt-4 flex flex-wrap gap-2.5">
                    <a :href="result.enrollUrl" class="btn btn-primary" data-track="cta_click" data-track-label="{{ $place }} – Book a visit">Book a visit</a>
                    <a :href="result.classroom.url" class="btn btn-outline" data-track="cta_click" :data-track-label="`{{ $place }} – See ${result.classroom.name}`">
                        See the <span x-text="result.classroom.name"></span> class
                    </a>
                </div>
            </div>
        </template>

        <template x-if="result && result.status === 'too_young'">
            <div class="rounded-[1.4rem] border-2 border-teal bg-teal-50 p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white text-teal-700"><x-icon name="heart" class="size-6" /></span>
                    <div>
                        <p class="font-display text-xl font-semibold leading-snug">Not old enough yet</p>
                        <p class="mt-1 leading-relaxed text-ink-soft">
                            Your little one will be <strong class="text-ink" x-text="result.age"></strong> <span x-text="result.on"></span>.
                            @if ($youngest)
                                Our youngest class, {{ $youngest['name'] }}, starts at {{ \App\Models\Classroom::monthsLabel($youngest['min']) }}.
                            @endif
                            Join the waitlist and we’ll call you when a place fits.
                        </p>
                    </div>
                </div>
                <a :href="result.enrollUrl" class="btn btn-primary mt-4" data-track="cta_click" data-track-label="{{ $place }} – Join the waitlist">Join the waitlist</a>
            </div>
        </template>

        <template x-if="result && result.status === 'too_old'">
            <div class="rounded-[1.4rem] border-2 border-grape bg-grape-50 p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white text-grape"><x-icon name="sun" class="size-6" /></span>
                    <div>
                        <p class="font-display text-xl font-semibold leading-snug">Your child is past nursery age</p>
                        <p class="mt-1 leading-relaxed text-ink-soft">
                            At <strong class="text-ink" x-text="result.age"></strong> they’re ready for big school. Our holiday camps welcome children aged 4 to 12.
                        </p>
                    </div>
                </div>
                <a href="{{ $config['campsUrl'] }}" class="btn btn-primary mt-4" data-track="cta_click" data-track-label="{{ $place }} – See holiday camps">See our holiday camps</a>
            </div>
        </template>
    </div>
</div>
