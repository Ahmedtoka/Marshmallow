@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
    <x-admin.page-header title="Settings" subtitle="Site-wide details the public website reads: contact numbers, hours, admissions and more.">
        <x-slot:actions>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View website</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        <nav class="-mx-4 flex gap-1 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-col lg:overflow-visible lg:px-0" aria-label="Settings groups">
            @foreach ($groups as $key => $g)
                <a href="{{ route('admin.content.settings.edit', $key) }}"
                   @class([
                       'flex h-10 shrink-0 items-center gap-2.5 whitespace-nowrap rounded-xl border px-3 text-sm font-bold transition-colors',
                       'border-line bg-white text-ink shadow-sm' => $key === $group,
                       'border-transparent text-muted hover:bg-white/70 hover:text-ink' => $key !== $group,
                   ])>
                    <x-icon :name="$g['icon']" class="size-4" /> {{ $g['label'] }}
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.content.settings.update', $group) }}" enctype="multipart/form-data" class="min-w-0">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-pad border-b border-line">
                    <h2 class="card-title">{{ $schema['label'] }}</h2>
                    <p class="mt-0.5 text-sm text-muted">{{ $schema['description'] }}</p>
                </div>

                <div class="card-pad grid gap-5 md:grid-cols-2">
                    @foreach ($schema['fields'] as $field)
                        @php
                            $key = $field['key'];
                            $value = $values[$key] ?? null;
                            $wide = in_array($field['type'], ['textarea', 'code', 'image', 'toggle'], true) || $key === 'admission_years' || $key === 'announcement';
                        @endphp
                        <div @class(['md:col-span-2' => $wide])>
                            @switch($field['type'])
                                @case('toggle')
                                    <x-admin.toggle :name="$key" :label="$field['label']" :checked="$value === '1'" :hint="$field['hint']" />
                                    @break

                                @case('image')
                                    <x-admin.image :name="$key" :label="$field['label']" :path="$value" :hint="$field['hint']"
                                        class="max-w-sm" :aspect="$key === 'logo_path' ? 'aspect-square max-w-40' : 'aspect-video'"
                                        :fit="$key === 'logo_path' ? 'object-contain' : 'object-cover'" />
                                    @break

                                @case('textarea')
                                    <x-admin.textarea :name="$key" :label="$field['label']" :value="$value" :hint="$field['hint']" :rows="$field['rows'] ?? 4" />
                                    @break

                                @case('code')
                                    <label class="label" for="{{ $key }}">{{ $field['label'] }}</label>
                                    @if (! empty($field['warning']))
                                        <div class="mb-2 flex gap-2 rounded-xl border border-honey/40 bg-honey/10 px-3 py-2.5 text-[13px] font-semibold text-[#8A5D00]">
                                            <x-icon name="alert" class="mt-0.5 size-4" /> <span>{{ $field['warning'] }}</span>
                                        </div>
                                    @endif
                                    <textarea id="{{ $key }}" name="{{ $key }}" rows="8" spellcheck="false"
                                        @class(['input font-mono text-xs', 'input-error' => $errors->has($key)])>{{ old($key, $value) }}</textarea>
                                    @if ($field['hint']) <p class="hint">{{ $field['hint'] }}</p> @endif
                                    @error($key) <p class="error">{{ $message }}</p> @enderror
                                    @break

                                @default
                                    <x-admin.input :name="$key" :label="$field['label']" :value="$value" :hint="$field['hint']"
                                        :type="in_array($field['type'], ['number', 'url', 'email', 'tel'], true) ? $field['type'] : 'text'"
                                        :placeholder="$field['placeholder'] ?? null" :required="$key === 'site_name'" />
                            @endswitch
                        </div>
                    @endforeach
                </div>
            </div>

            @include('admin.content.partials.save-bar', ['label' => 'Save '.mb_strtolower($schema['label']), 'note' => 'Changes appear on the website straight away.'])
        </form>
    </div>
@endsection
