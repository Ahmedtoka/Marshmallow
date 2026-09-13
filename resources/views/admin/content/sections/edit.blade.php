@extends('layouts.admin')
@section('title', 'Edit '.$section->name)

@section('content')
    <x-admin.page-header :title="$section->name.' section'" :subtitle="$hint" :back="route('admin.content.sections.index')">
        <x-slot:actions>
            <a href="{{ route('home') }}#{{ $section->key }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> Preview homepage</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.content.sections.update', $section) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Text</h2>
                    <x-admin.input name="title" label="Heading" :value="$section->title" hint="The main heading of this section." />
                    <x-admin.textarea name="subtitle" label="Intro text" :value="$section->subtitle" rows="3" hint="The short paragraph under the heading. Leave empty to hide it." />
                    <x-admin.textarea name="body" label="Extra text" :value="$section->body" rows="5" hint="Only used by some sections. Leave an empty line between paragraphs." />
                </div>

                <div class="card card-pad space-y-5">
                    <div>
                        <h2 class="card-title">Button</h2>
                        <p class="hint">Leave both empty to hide the button.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="button_text" label="Button text" :value="$section->button_text" placeholder="Book a visit" />
                        <x-admin.input name="button_url" label="Button link" :value="$section->button_url" placeholder="/enroll" hint="A page on this website like /enroll, or a full https:// link." />
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_visible" label="Show on the homepage" :checked="$section->is_visible" />
                    <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 rounded-xl bg-canvas px-3 py-2.5 text-sm">
                        <dt class="font-semibold text-muted">Name</dt><dd class="font-bold">{{ $section->name }}</dd>
                        <dt class="font-semibold text-muted">Key</dt><dd><code class="font-mono text-xs">{{ $section->key }}</code></dd>
                    </dl>
                    <p class="hint">The name and key are fixed because the website design uses them.</p>
                </div>

                <div class="card card-pad">
                    <x-admin.image name="image" label="Image" :path="$section->image" hint="Optional. Only some sections show an image (for example the hero). Landscape, at least 1600 px wide." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['cancel' => route('admin.content.sections.index')])
    </form>
@endsection
