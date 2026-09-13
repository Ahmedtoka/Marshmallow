@extends('layouts.admin')
@section('title', 'Homepage sections')

@section('content')
    <x-admin.page-header title="Homepage sections" subtitle="Drag sections to change their order on the homepage. Switch one off to hide it.">
        <x-slot:actions>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> Preview homepage</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        @if ($sections->isEmpty())
            <x-admin.empty icon="layers" title="No sections yet" text="Homepage sections are created by the website setup (SettingsSeeder)." />
        @else
            <ul data-sortable="{{ route('admin.content.sections.reorder') }}" class="divide-y divide-line">
                @foreach ($sections as $section)
                    <li data-id="{{ $section->id }}" class="flex items-center gap-2 bg-white px-2 py-3 sm:gap-3 sm:px-4"
                        x-data="{ on: {{ $section->is_visible ? 'true' : 'false' }}, busy: false }">
                        <button type="button" data-handle class="grid size-9 shrink-0 cursor-grab place-items-center rounded-lg text-muted hover:bg-canvas active:cursor-grabbing" title="Drag to reorder" aria-label="Drag to reorder">
                            <x-icon name="grip" />
                        </button>
                        <div class="min-w-0 flex-1" :class="!on && 'opacity-55'">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="font-bold text-ink">{{ $section->name }}</span>
                                <span x-show="!on" x-cloak class="badge badge-muted">Hidden</span>
                            </div>
                            @if ($section->title)
                                <p class="truncate text-sm text-ink/80">{{ $section->title }}</p>
                            @endif
                            @if (! empty($hints[$section->key]))
                                <p class="mt-0.5 hidden text-xs text-muted md:block">{{ $hints[$section->key] }}</p>
                            @endif
                        </div>
                        <button type="button" role="switch" :aria-checked="on.toString()" :disabled="busy" :title="on ? 'Visible on the homepage' : 'Hidden from the homepage'"
                            @click="busy = true; csrfFetch('{{ route('admin.content.sections.toggle', $section) }}', { method: 'PATCH' })
                                .then(r => r.ok ? r.json() : Promise.reject())
                                .then(d => on = d.is_visible)
                                .catch(() => alert('Could not save. Please refresh the page and try again.'))
                                .finally(() => busy = false)"
                            :class="on ? 'bg-brand' : 'bg-line'" class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors disabled:opacity-60">
                            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'" class="mt-0.5 inline-block size-5 rounded-full bg-white shadow transition-transform"></span>
                        </button>
                        <a href="{{ route('admin.content.sections.edit', $section) }}" class="btn btn-secondary btn-sm">
                            <x-icon name="pencil" class="size-4" /><span class="hidden sm:inline">Edit</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <p class="hint mt-3">Order and visibility save automatically.</p>
@endsection
