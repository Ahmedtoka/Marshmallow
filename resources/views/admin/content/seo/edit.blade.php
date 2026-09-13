@extends('layouts.admin')
@section('title', 'SEO · '.$page->label)

@php
    $routeName = \App\Http\Controllers\Admin\Content\SeoController::ROUTES[$page->page_key] ?? null;
    $pageUrl = $routeName && Route::has($routeName) ? route($routeName) : url('/');
@endphp

@section('content')
    <x-admin.page-header :title="$page->label" subtitle="How this page appears on Google and when it’s shared." :back="route('admin.content.seo.index')">
        <x-slot:actions>
            <a href="{{ $pageUrl }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View page</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.content.seo.update', $page) }}" enctype="multipart/form-data"
          x-data="{ title: @js((string) old('title', $page->title)), description: @js((string) old('description', $page->description)) }">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <div>
                        <x-admin.input name="title" label="Page title" :value="$page->title" x-model="title" maxlength="255" />
                        <p class="hint flex justify-between gap-3">
                            <span>The blue headline on Google and the browser tab text.</span>
                            <b :class="title.length > 60 ? 'text-red-600' : 'text-muted'" x-text="title.length + ' / 60'"></b>
                        </p>
                    </div>
                    <div>
                        <x-admin.textarea name="description" label="Description" :value="$page->description" rows="3" x-model="description" maxlength="1000" />
                        <p class="hint flex justify-between gap-3">
                            <span>The grey text under the title on Google, and the preview text when shared.</span>
                            <b :class="description.length > 160 ? 'text-red-600' : 'text-muted'" x-text="description.length + ' / 160'"></b>
                        </p>
                    </div>
                </div>

                <div class="card card-pad">
                    <p class="mb-3 text-xs font-bold text-muted">Google preview</p>
                    <div class="max-w-xl">
                        <p class="truncate text-xs text-[#202124]">{{ preg_replace('#^https?://#', '', $pageUrl) }}</p>
                        <p class="truncate text-lg leading-snug text-[#1a0dab]" x-text="title.length > 60 ? title.slice(0, 60) + '…' : (title || @js($page->label))"></p>
                        <p class="line-clamp-2 text-sm text-[#4d5156]" x-text="description.length > 160 ? description.slice(0, 160) + '…' : description"></p>
                    </div>
                </div>
            </div>

            <div class="min-w-0 space-y-6">
                <div class="card card-pad">
                    <x-admin.image name="og_image" label="Sharing image" :path="$page->og_image" aspect="aspect-[1200/630]"
                        hint="Shown when this page is shared on WhatsApp or Facebook. 1200 × 630 px. If empty, the default sharing image from Settings is used." />
                </div>
                <div class="card card-pad text-sm">
                    <p class="text-xs font-bold text-muted">Page key</p>
                    <code class="font-mono text-xs">{{ $page->page_key }}</code>
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['cancel' => route('admin.content.seo.index')])
    </form>
@endsection
