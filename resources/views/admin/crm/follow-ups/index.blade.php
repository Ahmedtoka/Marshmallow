@extends('layouts.admin')
@section('title', 'Follow-ups')
@section('content')
    <x-admin.page-header title="Follow-ups" subtitle="Calls, messages and tours you promised families." />

    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
        <div class="-mx-4 px-4 sm:mx-0 sm:px-0 overflow-x-auto w-full sm:w-auto">
            <nav class="inline-flex gap-1 rounded-xl bg-white border border-line p-1 min-w-max">
                @foreach (\App\Http\Controllers\Admin\Crm\FollowUpController::TABS as $key => $label)
                    <a href="{{ route('admin.crm.follow-ups.index', array_merge(request()->except(['page', 'tab']), ['tab' => $key])) }}"
                       @class(['flex items-center gap-2 rounded-lg px-3 h-9 text-sm font-bold',
                               'bg-ink text-white' => $tab === $key, 'text-muted hover:text-ink' => $tab !== $key])>
                        {{ $label }}
                        <span @class(['rounded-full px-1.5 text-xs leading-5',
                                      'bg-red-500 text-white' => $key === 'overdue' && $counts[$key] > 0,
                                      'bg-white/20' => $tab === $key && ! ($key === 'overdue' && $counts[$key] > 0),
                                      'bg-canvas' => $tab !== $key && ! ($key === 'overdue' && $counts[$key] > 0)])>{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
        @if ($isManager)
            <form method="GET" class="flex gap-2 w-full sm:w-auto">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="agent" class="input h-9 flex-1 sm:w-auto" onchange="this.form.submit()" aria-label="Agent">
                    <option value="">Everyone</option>
                    @foreach ($agents as $k => $v)<option value="{{ $k }}" @selected(request('agent') == $k)>{{ $v }}</option>@endforeach
                </select>
                <select name="branch" class="input h-9 flex-1 sm:w-auto" onchange="this.form.submit()" aria-label="Branch">
                    <option value="">All branches</option>
                    @foreach ($branches as $k => $v)<option value="{{ $k }}" @selected(request('branch') == $k)>{{ $v }}</option>@endforeach
                </select>
            </form>
        @endif
    </div>

    <div class="card">
        @if ($followUps->isEmpty())
            @php
                $empty = [
                    'overdue' => ['All caught up', 'Nothing is overdue. Nice work.'],
                    'today' => ['Nothing else due today', 'Schedule follow-ups from a lead page when you log a call.'],
                    'upcoming' => ['No upcoming follow-ups', 'Open a lead and use "Schedule next follow-up" so no family is forgotten.'],
                    'completed' => ['Nothing completed yet', 'Completed follow-ups and their outcomes will show here.'],
                ][$tab];
            @endphp
            <x-admin.empty icon="calendar" :title="$empty[0]" :text="$empty[1]">
                <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary">Go to leads</a>
            </x-admin.empty>
        @else
            <ul class="divide-y divide-line">
                @foreach ($followUps as $f)
                    @php $lead = $f->lead; $overdue = $f->isOverdue(); @endphp
                    <li class="p-4 flex flex-wrap sm:flex-nowrap items-start gap-3">
                        <div @class(['w-[88px] shrink-0 rounded-xl px-2 py-2 text-center', 'bg-red-50 text-red-600' => $overdue, 'bg-canvas text-ink' => ! $overdue])
                             title="{{ $f->due_at->format('D j M Y, g:i A') }}">
                            @if ($tab === 'completed')
                                <div class="text-[11px] font-bold">Done</div>
                                <div class="text-sm font-bold">{{ $f->completed_at->format('j M') }}</div>
                            @else
                                <div class="text-[11px] font-bold">{{ $f->due_at->isToday() ? 'Today' : ($f->due_at->isTomorrow() ? 'Tomorrow' : $f->due_at->format('D j M')) }}</div>
                                <div class="text-sm font-bold">{{ $f->due_at->format('g:i A') }}</div>
                                @if ($overdue)<div class="text-[10px] font-bold">{{ $f->due_at->diffForHumans(short: true) }}</div>@endif
                            @endif
                        </div>
                        <div class="flex-1 min-w-0 basis-40">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="badge badge-muted">{{ $f->typeLabel() }}</span>
                                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="font-bold hover:text-brand">{{ $lead->parent_name }}</a>
                                <x-admin.crm.status-badge :status="$lead->status" />
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-muted">
                                @if ($lead->child_name)<span class="font-semibold text-ink">{{ $lead->child_name }}</span>@endif
                                <x-admin.crm.class-chip :classroom="$lead->classroom" />
                                <span>{{ $lead->branch?->short_name ?: $lead->branch?->name }}</span>
                                @if ($isManager)<span>· {{ $f->user?->name ?? 'Unassigned' }}</span>@endif
                            </div>
                            @if ($f->notes)<p class="mt-1 text-sm">{{ $f->notes }}</p>@endif
                            @if ($tab === 'completed' && $f->outcome)<p class="mt-1 text-sm rounded-lg bg-canvas px-2.5 py-1.5">{{ $f->outcome }}</p>@endif
                        </div>
                        <div class="flex items-center gap-1.5 w-full sm:w-auto justify-end">
                            <x-admin.crm.contact-buttons :lead="$lead" />
                            @unless ($f->completed_at)
                                <button type="button" class="btn btn-success btn-sm h-9 sm:h-8"
                                        @click="$dispatch('complete-follow-up', { url: @js(route('admin.crm.follow-ups.complete', $f)), title: @js($f->typeLabel().' '.$lead->parent_name.' · '.$f->due_at->format('D j M, g:i A')) })">
                                    <x-icon name="check" class="size-4" /> Complete
                                </button>
                            @endunless
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="mt-4">{{ $followUps->links() }}</div>

    <x-admin.crm.complete-modal />
@endsection
