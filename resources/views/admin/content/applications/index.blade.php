@extends('layouts.admin')
@section('title', 'Job applications')

@section('content')
    <x-admin.page-header title="Careers" subtitle="Applications sent from the Careers page, newest first." />

    @include('admin.content.partials.careers-tabs', ['active' => 'applications'])

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-48">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input" onchange="this.form.submit()">
                <option value="">All statuses ({{ $counts->sum() }})</option>
                @foreach (\App\Models\JobApplication::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }} ({{ $counts[$key] ?? 0 }})</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-64">
            <label for="opening" class="label">Opening</label>
            <select id="opening" name="opening" class="input" onchange="this.form.submit()">
                <option value="">All openings</option>
                @foreach ($openings as $id => $title)
                    <option value="{{ $id }}" @selected($opening === $id)>{{ $title }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button class="btn btn-secondary">Filter</button></noscript>
        @if ($status || $opening)
            <a href="{{ route('admin.content.applications.index') }}" class="btn btn-ghost">Clear filters</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        @if ($applications->isEmpty())
            <x-admin.empty icon="file" :title="$status || $opening ? 'No applications match these filters' : 'No applications yet'"
                text="Applications from the Careers page will appear here." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Applicant</th><th>Position</th><th>Branch</th><th>CV</th><th>Received</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                            @php
                                $badge = match ($application->status) { 'new' => 'badge-pink', 'hired' => 'badge-green', 'rejected' => 'badge-red', default => 'badge-muted' };
                            @endphp
                            <tr>
                                <td class="min-w-48">
                                    <a href="{{ route('admin.content.applications.show', $application) }}" class="font-bold hover:text-brand">{{ $application->name }}</a>
                                    <p class="text-xs text-muted">{{ $application->phone }}</p>
                                </td>
                                <td class="whitespace-nowrap">{{ $application->jobOpening?->title ?? $application->position }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $application->branch?->name ?? '—' }}</td>
                                <td>
                                    @if ($application->cv_path)
                                        <a href="{{ route('admin.content.applications.show', ['application' => $application, 'download' => 'cv']) }}" class="btn btn-ghost btn-sm px-2" title="Download CV"><x-icon name="download" class="size-4" /></a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-muted" title="{{ $application->created_at->format('j M Y, g:i a') }}">{{ $application->created_at->diffForHumans() }}</td>
                                <td><span class="badge {{ $badge }}">{{ \App\Models\JobApplication::STATUSES[$application->status] ?? $application->status }}</span></td>
                                <td class="text-right"><a href="{{ route('admin.content.applications.show', $application) }}" class="btn btn-secondary btn-sm">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($applications->hasPages())
                <div class="border-t border-line px-4 py-3">{{ $applications->links() }}</div>
            @endif
        @endif
    </div>
@endsection
