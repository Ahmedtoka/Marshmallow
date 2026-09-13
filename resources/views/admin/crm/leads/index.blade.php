@extends('layouts.admin')
@section('title', 'Leads')
@section('content')
    @php
        $tabQuery = fn ($v) => array_merge(request()->except(['page', 'view']), ['view' => $v]);
        $activeFilterCount = collect(request()->only(['q', 'status', 'branch', 'class', 'interest', 'source', 'assignee', 'from', 'to']))->filter()->count();
    @endphp

    <x-admin.page-header title="Leads" subtitle="Every enquiry from the website, phone, WhatsApp and walk-ins.">
        <x-slot:actions>
            @if ($isManager)
                <a href="{{ route('admin.crm.leads.export', request()->except('page') + ['view' => $view]) }}" class="btn btn-secondary"><x-icon name="download" class="size-4" /> <span class="hidden sm:inline">Export CSV</span></a>
            @endif
            <a href="{{ route('admin.crm.leads.board') }}" class="btn btn-secondary"><x-icon name="columns" class="size-4" /> <span class="hidden sm:inline">Board</span></a>
            <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add lead</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Quick views --}}
    <div class="-mx-4 px-4 lg:mx-0 lg:px-0 overflow-x-auto mb-4">
        <nav class="flex gap-1 border-b border-line min-w-max">
            @foreach (\App\Http\Controllers\Admin\Crm\LeadController::VIEWS as $key => $label)
                <a href="{{ route('admin.crm.leads.index', $tabQuery($key)) }}"
                   @class(['flex items-center gap-2 px-3 h-11 -mb-px border-b-2 text-sm font-bold whitespace-nowrap',
                           'border-brand text-ink' => $view === $key, 'border-transparent text-muted hover:text-ink' => $view !== $key])>
                    {{ $label }}
                    <span @class(['rounded-full px-2 text-xs leading-5',
                                  'bg-red-50 text-red-600' => $key === 'overdue' && $counts[$key] > 0,
                                  'bg-ink text-white' => $view === $key && ! ($key === 'overdue' && $counts[$key] > 0),
                                  'bg-canvas text-muted' => $view !== $key && ! ($key === 'overdue' && $counts[$key] > 0)])>{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card mb-4" x-data="{ open: {{ $filtersActive ? 'true' : 'false' }} }">
        <input type="hidden" name="view" value="{{ $view }}">
        <div class="flex items-center gap-2 p-3">
            <div class="relative flex-1">
                <x-icon name="search" class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search reference, parent, phone or child" class="input pl-9">
            </div>
            <button type="button" class="btn btn-secondary md:hidden" @click="open = !open">
                <x-icon name="funnel" class="size-4" /> Filters @if ($activeFilterCount)<span class="badge badge-pink">{{ $activeFilterCount }}</span>@endif
            </button>
            <button class="btn btn-secondary hidden md:inline-flex">Search</button>
        </div>
        <div class="border-t border-line p-3 grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-2 md:!grid" :class="open ? 'grid' : 'hidden'">
            <select name="status" class="input" aria-label="Status">
                <option value="">Any status</option>
                @foreach (\App\Models\Lead::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach
            </select>
            <select name="branch" class="input" aria-label="Branch">
                <option value="">All branches</option>
                @foreach ($options['branches'] as $k => $v)<option value="{{ $k }}" @selected(request('branch') == $k)>{{ $v }}</option>@endforeach
            </select>
            <select name="class" class="input" aria-label="Class">
                <option value="">All classes</option>
                @foreach ($options['classrooms'] as $c)<option value="{{ $c->id }}" @selected(request('class') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <select name="interest" class="input" aria-label="Interest">
                <option value="">Any interest</option>
                @foreach (\App\Models\Lead::INTERESTS as $k => $v)<option value="{{ $k }}" @selected(request('interest') === $k)>{{ $v }}</option>@endforeach
            </select>
            <select name="source" class="input" aria-label="Source">
                <option value="">Any source</option>
                @foreach ($options['sources'] as $k => $v)<option value="{{ $k }}" @selected(request('source') === $k)>{{ $v }}</option>@endforeach
            </select>
            @if ($isManager)
                <select name="assignee" class="input" aria-label="Assignee">
                    <option value="">Anyone</option>
                    <option value="none" @selected(request('assignee') === 'none')>Unassigned</option>
                    @foreach ($options['agents'] as $k => $v)<option value="{{ $k }}" @selected(request('assignee') == $k)>{{ $v }}</option>@endforeach
                </select>
            @endif
            <input type="date" name="from" value="{{ request('from') }}" class="input" aria-label="Created from" title="Created from">
            <input type="date" name="to" value="{{ request('to') }}" class="input" aria-label="Created to" title="Created to">
            <div class="col-span-2 md:col-span-4 xl:col-span-8 flex justify-end gap-2">
                @if ($filtersActive)
                    <a href="{{ route('admin.crm.leads.index', ['view' => $view]) }}" class="btn btn-ghost btn-sm">Clear filters</a>
                @endif
                <button class="btn btn-secondary btn-sm">Apply filters</button>
            </div>
        </div>
    </form>

    @if ($leads->isEmpty())
        <div class="card">
            @if ($filtersActive)
                <x-admin.empty icon="search" title="No leads match these filters" text="Try a different search or clear the filters.">
                    <a href="{{ route('admin.crm.leads.index', ['view' => $view]) }}" class="btn btn-secondary">Clear filters</a>
                </x-admin.empty>
            @else
                <x-admin.empty icon="users" title="Nothing in this view" text="New website enquiries land here automatically. You can also add a lead from a call or walk-in.">
                    <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add lead</a>
                </x-admin.empty>
            @endif
        </div>
    @else
        <form method="POST" id="bulk-form" x-data="{ selected: [] }"
              :action="selected.length ? @js(route('admin.crm.leads.assign', '__ID__')).replace('__ID__', selected[0]) : '#'">
            @csrf
            @method('PATCH')

            @if ($isManager)
                <div x-show="selected.length" x-cloak class="sticky top-16 z-10 mb-3 card p-3 flex flex-wrap items-center gap-2 shadow-lg shadow-ink/5">
                    <span class="font-bold"><span x-text="selected.length"></span> selected</span>
                    <select name="assigned_to" class="input h-9 w-auto flex-1 sm:flex-none sm:min-w-52" aria-label="Assign to">
                        <option value="">Unassigned</option>
                        @foreach ($options['agents'] as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                    <button class="btn btn-primary btn-sm h-9">Assign</button>
                    <button type="button" class="btn btn-ghost btn-sm h-9" @click="selected = []">Cancel</button>
                </div>
            @endif

            {{-- Desktop table --}}
            <div class="card overflow-hidden hidden md:block">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                        <tr>
                            @if ($isManager)
                                <th class="w-8"><input type="checkbox" class="checkbox" aria-label="Select all"
                                    @change="selected = $event.target.checked ? @js($leads->pluck('id')->map(fn ($i) => (string) $i)) : []"></th>
                            @endif
                            <th>Reference</th>
                            <th>Parent</th>
                            <th>Child</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Agent</th>
                            <th>Next follow-up</th>
                            <th>Source</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($leads as $lead)
                            <tr>
                                @if ($isManager)
                                    <td><input type="checkbox" class="checkbox" name="lead_ids[]" value="{{ $lead->id }}" x-model="selected" aria-label="Select {{ $lead->reference }}"></td>
                                @endif
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.crm.leads.show', $lead) }}" class="font-bold text-ink hover:text-brand">{{ $lead->reference }}</a>
                                    @if ($lead->priority === 'hot')<span class="badge badge-red ml-1">Hot</span>@endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.crm.leads.show', $lead) }}" class="block font-bold hover:text-brand">{{ $lead->parent_name }}</a>
                                    <span class="text-xs text-muted">{{ $lead->phone }}</span>
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $lead->child_name ?: '—' }} @if ($lead->childAgeLabel())<span class="text-xs text-muted font-normal">· {{ $lead->childAgeLabel() }}</span>@endif</div>
                                    <x-admin.crm.class-chip :classroom="$lead->classroom" class="mt-0.5" />
                                </td>
                                <td class="whitespace-nowrap">{{ $lead->branch?->short_name ?: $lead->branch?->name ?: '—' }}</td>
                                <td><x-admin.crm.status-badge :status="$lead->status" /></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-admin.crm.avatar :user="$lead->assignee" />
                                        <span class="text-xs whitespace-nowrap {{ $lead->assignee ? '' : 'text-muted' }}">{{ $lead->assignee?->name ?? 'Unassigned' }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    @if ($lead->next_follow_up_at)
                                        <span @class(['text-xs font-bold', 'text-red-600' => $lead->isOverdue()])>
                                            @if ($lead->isOverdue())<x-icon name="alert" class="size-3.5 inline -mt-0.5" /> Overdue · @endif
                                            <x-admin.crm.when :date="$lead->next_follow_up_at" />
                                        </span>
                                    @else
                                        <span class="text-muted text-xs">None</span>
                                    @endif
                                </td>
                                <td class="text-xs whitespace-nowrap">{{ $lead->channel === 'website' ? ($lead->source ? (\App\Models\Visit::SOURCES[$lead->source] ?? $lead->source) : 'Website') : (\App\Models\Lead::CHANNELS[$lead->channel] ?? $lead->channel) }}</td>
                                <td class="text-xs text-muted whitespace-nowrap"><x-admin.crm.when :date="$lead->created_at" short /></td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <x-admin.crm.contact-buttons :lead="$lead" />
                                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-secondary btn-sm size-8 px-0" aria-label="Open lead"><x-icon name="chevron-right" class="size-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden space-y-2">
                @foreach ($leads as $lead)
                    <div class="card p-4">
                        <div class="flex items-start gap-3">
                            @if ($isManager)
                                <input type="checkbox" class="checkbox mt-1" value="{{ $lead->id }}" x-model="selected" aria-label="Select {{ $lead->reference }}">
                            @endif
                            <a href="{{ route('admin.crm.leads.show', $lead) }}" class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold truncate">{{ $lead->parent_name }}</span>
                                    @if ($lead->priority === 'hot')<span class="badge badge-red">Hot</span>@endif
                                </div>
                                <div class="text-xs text-muted">{{ $lead->reference }} · {{ $lead->phone }}</div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    <x-admin.crm.status-badge :status="$lead->status" />
                                    <x-admin.crm.class-chip :classroom="$lead->classroom" />
                                    @if ($lead->child_name)<span class="text-xs font-semibold">{{ $lead->child_name }}@if ($lead->childAgeLabel()) · {{ $lead->childAgeLabel() }}@endif</span>@endif
                                </div>
                                <div class="mt-1.5 text-xs text-muted flex flex-wrap gap-x-3">
                                    <span>{{ $lead->branch?->short_name ?: $lead->branch?->name }}</span>
                                    <span>{{ $lead->assignee?->name ?? 'Unassigned' }}</span>
                                    <x-admin.crm.when :date="$lead->created_at" />
                                </div>
                                @if ($lead->next_follow_up_at)
                                    <div @class(['mt-1 text-xs font-bold', 'text-red-600' => $lead->isOverdue(), 'text-ink' => ! $lead->isOverdue()])>
                                        {{ $lead->isOverdue() ? 'Overdue follow-up' : 'Follow-up' }} · <x-admin.crm.when :date="$lead->next_follow_up_at" />
                                    </div>
                                @endif
                            </a>
                            <div class="flex flex-col gap-1.5">
                                <x-admin.crm.contact-buttons :lead="$lead" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>

        <div class="mt-4">{{ $leads->links() }}</div>
    @endif
@endsection
