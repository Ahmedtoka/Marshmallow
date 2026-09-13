{{-- Openings / Applications switch. Needs $active ('jobs'|'applications') and $newApplications. --}}
<div class="mb-5 inline-flex rounded-xl border border-line bg-white p-1">
    <a href="{{ route('admin.content.jobs.index') }}" @class(['inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-bold', 'bg-ink text-white' => $active === 'jobs', 'text-muted hover:text-ink' => $active !== 'jobs'])>
        <x-icon name="briefcase" class="size-4" /> Openings
    </a>
    <a href="{{ route('admin.content.applications.index') }}" @class(['inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-bold', 'bg-ink text-white' => $active === 'applications', 'text-muted hover:text-ink' => $active !== 'applications'])>
        <x-icon name="file" class="size-4" /> Applications
        @if ($newApplications)
            <span class="grid h-5 min-w-5 place-items-center rounded-full bg-brand px-1 text-[11px] text-white">{{ $newApplications }}</span>
        @endif
    </a>
</div>
