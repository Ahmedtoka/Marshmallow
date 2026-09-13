@extends('layouts.admin')
@section('title', $application->name)

@section('content')
    <x-admin.page-header :title="$application->name" :subtitle="'Applied for '.($application->jobOpening?->title ?? $application->position).' · '.$application->created_at->format('j M Y, g:i a')" :back="route('admin.content.applications.index')">
        <x-slot:actions>
            <x-admin.confirm-delete :action="route('admin.content.applications.destroy', $application)" size="" label="Delete application"
                message="Delete this application and its CV file? This can’t be undone." />
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="min-w-0 space-y-6 lg:col-span-2">
            <div class="card card-pad">
                <h2 class="card-title mb-4">Application</h2>
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold text-muted">Name</dt><dd class="font-semibold">{{ $application->name }}</dd></div>
                    <div><dt class="text-xs font-bold text-muted">Position</dt><dd class="font-semibold">{{ $application->position }}</dd></div>
                    <div><dt class="text-xs font-bold text-muted">Phone</dt><dd class="font-semibold"><a href="{{ tel_link($application->phone) }}" class="hover:text-brand">{{ $application->phone }}</a></dd></div>
                    <div><dt class="text-xs font-bold text-muted">Email</dt><dd class="font-semibold break-all">@if ($application->email)<a href="mailto:{{ $application->email }}" class="hover:text-brand">{{ $application->email }}</a>@else — @endif</dd></div>
                    <div><dt class="text-xs font-bold text-muted">Opening</dt><dd class="font-semibold">@if ($application->jobOpening)<a href="{{ route('admin.content.jobs.edit', $application->jobOpening) }}" class="hover:text-brand">{{ $application->jobOpening->title }}</a>@else General application @endif</dd></div>
                    <div><dt class="text-xs font-bold text-muted">Preferred branch</dt><dd class="font-semibold">{{ $application->branch?->name ?? 'Any' }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold text-muted">Message</dt>
                        <dd class="mt-1 whitespace-pre-line rounded-xl bg-canvas px-4 py-3 text-ink/90">{{ $application->message ?: 'No message.' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="min-w-0 space-y-6">
            <form method="POST" action="{{ route('admin.content.applications.update', $application) }}" class="card card-pad space-y-4">
                @csrf
                @method('PATCH')
                <h2 class="card-title">Status</h2>
                <x-admin.select name="status" :options="\App\Models\JobApplication::STATUSES" :value="$application->status" />
                <button type="submit" class="btn btn-primary w-full"><x-icon name="check" class="size-4" /> Update status</button>
            </form>

            <div class="card card-pad space-y-3">
                <h2 class="card-title">CV</h2>
                @if ($hasCv)
                    <a href="{{ route('admin.content.applications.show', ['application' => $application, 'download' => 'cv']) }}" class="btn btn-secondary w-full"><x-icon name="download" class="size-4" /> Download CV</a>
                @elseif ($application->cv_path)
                    <p class="text-sm text-red-600">The CV file is missing from storage.</p>
                @else
                    <p class="text-sm text-muted">No CV was attached.</p>
                @endif
            </div>

            <div class="card card-pad space-y-2">
                <h2 class="card-title">Contact</h2>
                <a href="{{ tel_link($application->phone) }}" class="btn btn-secondary w-full"><x-icon name="phone" class="size-4" /> Call</a>
                <a href="{{ whatsapp_link($application->phone) }}" target="_blank" rel="noopener" class="btn btn-secondary w-full"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
            </div>
        </div>
    </div>
@endsection
