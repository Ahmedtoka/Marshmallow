@extends('layouts.admin')
@section('title', $activity->exists ? $activity->name : 'Add activity')

@section('content')
    <x-admin.page-header :title="$activity->exists ? $activity->name : 'Add activity'"
        :subtitle="$activity->exists ? $activity->categoryLabel().' · shown on the Activities page and on the classes that include it' : 'Something children do, like gymnastics or storytelling.'"
        :back="route('admin.content.activities.index')">
        @if ($activity->exists)
            <x-slot:actions>
                <a href="{{ route('activities.show', $activity) }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
                <x-admin.confirm-delete :action="route('admin.content.activities.destroy', $activity)" size=""
                    :message="'Delete '.$activity->name.'? It will be removed from every class, with all its photos. This can’t be undone.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $activity->exists ? route('admin.content.activities.update', $activity) : route('admin.content.activities.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($activity->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">About the activity</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name" label="Name" :value="$activity->name" required placeholder="Gymnastics" />
                        <x-admin.select name="category" label="Category" :options="\App\Models\Activity::CATEGORIES" :value="$activity->category" required hint="Groups activities on the Activities page." />
                    </div>
                    <x-admin.input name="slug" label="Web address" :value="$activity->slug" placeholder="made from the name" hint="The end of the activity page link. Leave empty to make it from the name." />
                    <x-admin.textarea name="summary" label="Summary" :value="$activity->summary" rows="3" hint="One or two sentences shown on activity cards." />
                    <x-admin.textarea name="description" label="Full description" :value="$activity->description" rows="6" hint="Shown on the activity page. Leave an empty line between paragraphs." />
                </div>

                <div class="card card-pad">
                    <x-admin.icon-picker name="icon" label="Icon" :value="$activity->icon" hint="Shown on activity cards and in class activity lists." />
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_active" label="Active" :checked="$activity->is_active" hint="Hidden activities don’t appear anywhere on the website." />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$activity->sort_order" hint="Lower numbers show first." />
                </div>
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Look</h2>
                    <x-admin.color name="color" label="Color" :value="$activity->color" />
                    <x-admin.image name="cover_image" label="Cover photo" :path="$activity->cover_image" hint="Top of the activity page. Landscape, at least 1200 px wide." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $activity->exists ? 'Save activity' : 'Add activity', 'cancel' => route('admin.content.activities.index')])
    </form>

    @if ($activity->exists)
        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            <x-admin.photos :model="$activity" type="activity" class="min-w-0 lg:col-span-2"
                hint="General photos for the activity page. For photos from one class, open the activity from that class." />

            <section class="card min-w-0 self-start overflow-hidden">
                <div class="card-pad border-b border-line">
                    <h2 class="card-title">Used in classes</h2>
                    <p class="hint">Add or remove it from a class on the class page.</p>
                </div>
                @if ($classrooms->isEmpty())
                    <x-admin.empty icon="cupcake" title="Not in any class yet" class="py-8" />
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($classrooms as $classroom)
                            <li class="flex items-center gap-3 px-4 py-3">
                                <span class="size-3 shrink-0 rounded-full" style="background: {{ $classroom->color }}"></span>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.content.classrooms.edit', $classroom) }}#activities" class="font-bold hover:text-brand">{{ $classroom->name }}</a>
                                    <p class="truncate text-xs text-muted">{{ $classroom->pivot->frequency ?: '—' }}</p>
                                </div>
                                <a href="{{ route('admin.content.classroom-activities.edit', $classroom->pivot->id) }}" class="btn btn-ghost btn-sm" title="Photos in this class">
                                    <x-icon name="camera" class="size-4" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    @endif
@endsection
