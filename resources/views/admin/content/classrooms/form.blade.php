@extends('layouts.admin')
@section('title', $classroom->exists ? $classroom->name : 'Add class')

@section('content')
    <x-admin.page-header
        :title="$classroom->exists ? $classroom->name : 'Add class'"
        :subtitle="$classroom->exists ? $classroom->ageRangeLabel().' · '.($classroom->is_active ? 'Active' : 'Hidden from the website') : 'A new age group with its own page on the website.'"
        :back="route('admin.content.classrooms.index')">
        @if ($classroom->exists)
            <x-slot:actions>
                <a href="{{ route('classes.show', $classroom) }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
                <x-admin.confirm-delete :action="route('admin.content.classrooms.destroy', $classroom)" size=""
                    :message="'Delete the '.$classroom->name.' class, its activities list and all its photos? This can’t be undone.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $classroom->exists ? route('admin.content.classrooms.update', $classroom) : route('admin.content.classrooms.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($classroom->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Main --}}
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">About the class</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name" label="Class name" :value="$classroom->name" required placeholder="Cupcake" />
                        <x-admin.input name="slug" label="Web address" :value="$classroom->slug" placeholder="made from the name" hint="The end of the class page link, e.g. /classes/cupcake." />
                    </div>
                    <x-admin.input name="tagline" label="Tagline" :value="$classroom->tagline" placeholder="Tiny explorers taking their first big steps" hint="One short line shown under the class name on cards." />
                    <x-admin.textarea name="summary" label="Summary" :value="$classroom->summary" rows="3" hint="Shown on the class card and in the class finder result." />
                    <x-admin.textarea name="description" label="Full description" :value="$classroom->description" rows="6" hint="Shown on the class page. Leave an empty line between paragraphs." />
                </div>

                <div class="card card-pad space-y-5"
                     x-data="{ min: @js((string) old('min_months', $classroom->min_months)), max: @js((string) old('max_months', $classroom->max_months)),
                        fmt(m) { m = parseInt(m, 10); if (isNaN(m)) return ''; if (m < 12) return m + ' months'; const y = Math.round(m / 12 * 10) / 10; return y + (y === 1 ? ' year' : ' years'); } }">
                    <div>
                        <h2 class="card-title">Ages</h2>
                        <p class="hint">Ages are in months. A child moves up to the next class on the month the range ends, so Cupcake 9–24 and Popcorn 24–30 fit together without a gap.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-admin.input name="min_months" label="From (months)" type="number" min="0" max="216" :value="$classroom->min_months" required x-model="min" />
                            <p class="mt-1 text-xs font-bold text-grape" x-show="fmt(min)" x-text="'= ' + fmt(min)"></p>
                        </div>
                        <div>
                            <x-admin.input name="max_months" label="Up to (months)" type="number" min="1" max="216" :value="$classroom->max_months" x-model="max" hint="Leave empty for “up to school age”." />
                            <p class="mt-1 text-xs font-bold text-grape" x-text="max ? '= ' + fmt(max) : '= up to school age'"></p>
                        </div>
                    </div>
                    <x-admin.input name="age_label" label="Age label for parents" :value="$classroom->age_label"
                        x-bind:placeholder="fmt(min) + ' – ' + (max ? fmt(max) : 'school age')"
                        hint="Shown to parents, e.g. “2.5 – 3 years”. Leave empty and it’s generated from the ages." />
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">What children learn</h2>
                    <x-admin.list-input name="goals" label="Goals for the year" :items="$classroom->goals ?? []" add-label="Add a goal"
                        placeholder="First words, gestures and songs in English" hint="Shown as a checklist on the class page. Press Enter to add another." />
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">A day in the class</h2>
                    <x-admin.routine-input name="daily_routine" :items="$classroom->daily_routine ?? []" hint="Shown as a timeline on the class page, in this order." />
                </div>

                <div class="card card-pad space-y-5">
                    <div>
                        <h2 class="card-title">Search engines</h2>
                        <p class="hint">Optional. How the class page appears on Google. Leave empty to use the class name and summary.</p>
                    </div>
                    <x-admin.input name="seo_title" label="Page title" :value="$classroom->seo_title" maxlength="255" />
                    <x-admin.textarea name="seo_description" label="Page description" :value="$classroom->seo_description" rows="2" maxlength="255" />
                </div>
            </div>

            {{-- Side --}}
            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_active" label="Active" :checked="$classroom->is_active" hint="Active classes show on the website and are used by the class finder." />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$classroom->sort_order" hint="Lower numbers show first." />
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Look</h2>
                    <x-admin.color name="color" label="Class color" :value="$classroom->color" hint="Used for the class card, badges and the age map." />
                    <x-admin.select name="icon" label="Candy icon" :options="$icons" :value="$classroom->icon" hint="The illustration used for this class on the website." />
                    <x-admin.image name="cover_image" label="Cover photo" :path="$classroom->cover_image" hint="Top of the class page and the class card. Landscape, at least 1200 px wide." />
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Class details</h2>
                    <x-admin.input name="teacher_ratio" label="Teacher ratio" :value="$classroom->teacher_ratio" placeholder="1 teacher : 6 children" />
                    <x-admin.input name="capacity" label="Places per class" type="number" min="0" :value="$classroom->capacity" hint="Optional. For your team; not shown to parents." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $classroom->exists ? 'Save class' : 'Add class', 'cancel' => route('admin.content.classrooms.index'), 'note' => $classroom->exists ? null : 'After saving you can add activities and photos.'])
    </form>

    @if ($classroom->exists)
        <div class="mt-10 grid gap-6 xl:grid-cols-2">
            {{-- Activities in this class --}}
            <section id="activities" class="card min-w-0 scroll-mt-24 self-start overflow-hidden">
                <div class="card-pad flex flex-wrap items-start justify-between gap-3 border-b border-line">
                    <div class="min-w-0">
                        <h2 class="card-title">Activities in this class</h2>
                        <p class="hint">Drag to change the order parents see. Open an activity to describe it for this class and add photos taken here.</p>
                    </div>
                    <span class="badge badge-muted">{{ $rows->count() }}</span>
                </div>

                @if ($rows->isEmpty())
                    <x-admin.empty icon="blocks" title="No activities yet" text="Add the activities this class does, like gymnastics or storytelling." class="py-10" />
                @else
                    <ul data-sortable="{{ route('admin.content.classrooms.activities.reorder', $classroom) }}" class="divide-y divide-line">
                        @foreach ($rows as $row)
                            <li data-id="{{ $row->id }}" class="flex items-center gap-2 bg-white px-2 py-2.5 sm:gap-3 sm:px-4">
                                <button type="button" data-handle class="grid size-8 shrink-0 cursor-grab place-items-center rounded-lg text-muted hover:bg-canvas active:cursor-grabbing" title="Drag to reorder" aria-label="Drag to reorder">
                                    <x-icon name="grip" class="size-4" />
                                </button>
                                <span class="grid size-9 shrink-0 place-items-center rounded-xl text-white" style="background: {{ $row->activity->color ?: '#8479BD' }}">
                                    <x-icon :name="$row->activity->icon" class="size-5" />
                                </span>
                                <a href="{{ route('admin.content.classroom-activities.edit', $row) }}" class="min-w-0 flex-1">
                                    <span class="block truncate font-bold text-ink hover:text-brand">{{ $row->activity->name }}</span>
                                    <span class="block truncate text-xs text-muted">
                                        @if ($row->frequency)<b class="font-bold text-grape">{{ $row->frequency }}</b>@endif
                                        @if ($row->frequency && $row->details) · @endif
                                        {{ $row->details }}
                                    </span>
                                </a>
                                <span class="badge badge-muted hidden sm:inline-flex" title="Photos"><x-icon name="camera" class="size-3.5" /> {{ $row->photos_count }}</span>
                                <a href="{{ route('admin.content.classroom-activities.edit', $row) }}" class="btn btn-secondary btn-sm">Edit</a>
                                <x-admin.confirm-delete :action="route('admin.content.classroom-activities.destroy', $row)" icon label="Remove from class"
                                    :message="'Remove '.$row->activity->name.' from '.$classroom->name.'? Photos added for it in this class will be deleted.'" />
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('admin.content.classrooms.activities.store', $classroom) }}" class="space-y-3 border-t border-line bg-canvas/50 p-4">
                    @csrf
                    <p class="text-sm font-bold">Add an activity</p>
                    @if ($available->isEmpty())
                        <p class="text-sm text-muted">Every activity is already in this class. <a href="{{ route('admin.content.activities.create') }}" class="font-bold text-brand">Create a new activity</a>.</p>
                    @else
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="activity_id" class="sr-only">Activity</label>
                                <select id="activity_id" name="activity_id" required @class(['input', 'input-error' => $errors->has('activity_id')])>
                                    <option value="">Choose an activity…</option>
                                    @foreach ($available as $category => $activities)
                                        <optgroup label="{{ $category }}">
                                            @foreach ($activities as $activity)
                                                <option value="{{ $activity->id }}" @selected((string) old('activity_id') === (string) $activity->id)>{{ $activity->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('activity_id') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="frequency" class="sr-only">How often</label>
                                <input id="frequency" name="frequency" value="{{ old('frequency') }}" class="input" placeholder="How often, e.g. Twice a week" maxlength="255" list="frequency-options">
                                <datalist id="frequency-options">
                                    <option value="Daily"></option><option value="Twice a week"></option><option value="3 times a week"></option><option value="Weekly"></option><option value="Monthly"></option>
                                </datalist>
                            </div>
                            <div>
                                <label for="details" class="sr-only">Details</label>
                                <input id="details" name="details" value="{{ old('details') }}" class="input" placeholder="What they do (optional)" maxlength="2000">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><x-icon name="plus" class="size-4" /> Add to {{ $classroom->name }}</button>
                    @endif
                </form>
            </section>

            {{-- Class photos --}}
            <x-admin.photos :model="$classroom" type="classroom" title="Class photos" class="min-w-0 self-start"
                hint="Shown in the gallery on the class page. Drag to reorder; the starred photo is used first." />
        </div>
    @endif
@endsection
