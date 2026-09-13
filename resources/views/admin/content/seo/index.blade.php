@extends('layouts.admin')
@section('title', 'SEO')

@section('content')
    <x-admin.page-header title="Search & sharing" subtitle="How each page appears on Google and when shared on WhatsApp or Facebook." />

    <div class="card overflow-hidden">
        @if ($pages->isEmpty())
            <x-admin.empty icon="search" title="No pages yet" text="SEO pages are created by the website setup (SettingsSeeder)." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Page</th><th>Title</th><th>Description</th><th>Image</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($pages as $page)
                            @php $t = mb_strlen((string) $page->title); $d = mb_strlen((string) $page->description); @endphp
                            <tr>
                                <td class="whitespace-nowrap font-bold"><a href="{{ route('admin.content.seo.edit', $page) }}" class="hover:text-brand">{{ $page->label }}</a></td>
                                <td class="min-w-64">
                                    <p class="max-w-sm truncate">{{ $page->title ?: '—' }}</p>
                                    <p @class(['text-xs font-bold', 'text-red-600' => $t > 60, 'text-muted' => $t <= 60])>{{ $t }}/60</p>
                                </td>
                                <td class="min-w-72">
                                    <p class="max-w-md truncate text-muted">{{ $page->description ?: '—' }}</p>
                                    <p @class(['text-xs font-bold', 'text-red-600' => $d > 160, 'text-muted' => $d <= 160])>{{ $d }}/160</p>
                                </td>
                                <td>
                                    @if ($page->og_image)
                                        <img src="{{ media_url($page->og_image) }}" alt="" class="h-8 w-14 rounded object-cover">
                                    @else
                                        <span class="text-xs text-muted">Default</span>
                                    @endif
                                </td>
                                <td class="text-right"><a href="{{ route('admin.content.seo.edit', $page) }}" class="btn btn-secondary btn-sm">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <p class="hint mt-3">Titles over 60 characters and descriptions over 160 may be cut off on Google. Class, activity and camp pages use their own names and summaries.</p>
@endsection
