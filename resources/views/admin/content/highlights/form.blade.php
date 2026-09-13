@extends('layouts.admin')
@section('title', $highlight->exists ? 'Edit highlight' : 'Add highlight')

@section('content')
    <x-admin.page-header :title="$highlight->exists ? $highlight->title : 'Add highlight'" subtitle="A short card with an icon, a title and one sentence."
        :back="route('admin.content.highlights.index', ['group' => $highlight->group])">
        @if ($highlight->exists)
            <x-slot:actions>
                <x-admin.confirm-delete :action="route('admin.content.highlights.destroy', $highlight)" size="" :message="'Delete “'.$highlight->title.'”?'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $highlight->exists ? route('admin.content.highlights.update', $highlight) : route('admin.content.highlights.store') }}">
        @csrf
        @if ($highlight->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5" x-data="{ group: @js(old('group', $highlight->group)), where: @js($where) }">
                    <x-admin.select name="group" label="Group" :options="\App\Models\Highlight::GROUPS" :value="$highlight->group" required x-model="group" />
                    <p class="-mt-3 flex items-center gap-1.5 text-xs font-semibold text-[#146B73]"><x-icon name="eye" class="size-3.5" /> <span x-text="where[group]"></span></p>
                    <x-admin.input name="title" label="Title" :value="$highlight->title" required placeholder="Cameras in every room" />
                    <x-admin.textarea name="description" label="Description" :value="$highlight->description" rows="3" hint="One or two short sentences." />
                </div>
                <div class="card card-pad">
                    <x-admin.icon-picker name="icon" label="Icon" :value="$highlight->icon" />
                </div>
            </div>
            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_visible" label="Visible" :checked="$highlight->is_visible" />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$highlight->sort_order" hint="Lower numbers show first within the group." />
                </div>
                <div class="card card-pad">
                    <x-admin.color name="color" label="Icon color" :value="$highlight->color" />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $highlight->exists ? 'Save highlight' : 'Add highlight', 'cancel' => route('admin.content.highlights.index', ['group' => $highlight->group])])
    </form>
@endsection
