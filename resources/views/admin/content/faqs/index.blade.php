@extends('layouts.admin')
@section('title', 'FAQ')

@section('content')
    <x-admin.page-header title="Frequently asked questions" subtitle="Shown in the FAQ section of the homepage and used by search engines.">
        <x-slot:actions>
            <a href="{{ route('admin.content.faqs.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add question</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        @if ($faqs->isEmpty())
            <x-admin.empty icon="help" title="No questions yet" text="Add the questions parents ask most often on the phone.">
                <a href="{{ route('admin.content.faqs.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add question</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th class="w-16">Order</th><th>Question</th><th>Category</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($faqs as $faq)
                            <tr>
                                <td class="text-muted">{{ $faq->sort_order }}</td>
                                <td class="min-w-80">
                                    <a href="{{ route('admin.content.faqs.edit', $faq) }}" class="font-bold hover:text-brand">{{ $faq->question }}</a>
                                    <p class="max-w-2xl truncate text-xs text-muted">{{ $faq->answer }}</p>
                                </td>
                                <td class="whitespace-nowrap text-muted">{{ $faq->category ? ($categories[$faq->category] ?? $faq->category) : '—' }}</td>
                                <td>
                                    @if ($faq->is_visible)
                                        <span class="badge badge-green">Visible</span>
                                    @else
                                        <span class="badge badge-muted">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.content.faqs.edit', $faq) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <x-admin.confirm-delete :action="route('admin.content.faqs.destroy', $faq)" icon message="Delete this question?" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
