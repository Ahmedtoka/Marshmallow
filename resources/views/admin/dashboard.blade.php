@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    @php
        use App\Models\Lead;
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = \Illuminate\Support\Str::before(trim($user->name), ' ');
        $pipelineTotal = max(1, $pipeline->sum());
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
        <div>
            <h1 class="page-title">{{ $greeting }}, {{ $firstName }}</h1>
            <p class="page-subtitle">{{ now()->format('l, j F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.crm.follow-ups.index') }}" class="btn btn-secondary"><x-icon name="calendar" class="size-4" /> Follow-ups</a>
            <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add lead</a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <a href="{{ route('admin.crm.leads.index', ['view' => 'all', 'from' => now()->startOfMonth()->toDateString()]) }}" class="card p-4 hover:border-grape/40">
            <div class="stat-label">New leads this month</div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="stat-value">{{ $kpis['new_month'] }}</span>
                @if ($kpis['new_change'] !== null)
                    <span @class(['text-xs font-bold', 'text-[#557316]' => $kpis['new_change'] >= 0, 'text-red-600' => $kpis['new_change'] < 0])
                          title="Compared with the same days last month">{{ $kpis['new_change'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['new_change']) }}%</span>
                @endif
            </div>
        </a>
        <a href="{{ route('admin.crm.leads.index', ['view' => 'open']) }}" class="card p-4 hover:border-grape/40">
            <div class="stat-label">Open leads</div>
            <div class="stat-value mt-2">{{ $kpis['open'] }}</div>
        </a>
        <a href="{{ route('admin.crm.leads.index', ['view' => 'tour_booked']) }}" class="card p-4 hover:border-grape/40">
            <div class="stat-label">Tours booked this week</div>
            <div class="stat-value mt-2">{{ $kpis['tours_week'] }}</div>
        </a>
        <a href="{{ route('admin.crm.leads.index', ['view' => 'enrolled']) }}" class="card p-4 hover:border-grape/40">
            <div class="stat-label">Enrolled this month</div>
            <div class="stat-value mt-2 text-lime">{{ $kpis['enrolled_month'] }}</div>
        </a>
    </div>

    {{-- Pipeline strip --}}
    <section class="card p-4 mb-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="card-title">Pipeline</h2>
            <a href="{{ route('admin.crm.leads.board') }}" class="text-xs font-bold text-muted hover:text-ink">Open board</a>
        </div>
        <div class="flex h-2.5 rounded-full overflow-hidden bg-canvas mb-3">
            @foreach (Lead::STATUSES as $status => $label)
                @if ($pipeline[$status] ?? 0)
                    <div style="width: {{ ($pipeline[$status] / $pipelineTotal) * 100 }}%; background: {{ Lead::STATUS_COLORS[$status] }}" title="{{ $label }}: {{ $pipeline[$status] }}"></div>
                @endif
            @endforeach
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            @foreach (Lead::STATUSES as $status => $label)
                <a href="{{ route('admin.crm.leads.index', ['view' => 'all', 'status' => $status]) }}" class="rounded-xl px-3 py-2 hover:bg-canvas">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-muted whitespace-nowrap"><span class="size-2 rounded-full" style="background: {{ Lead::STATUS_COLORS[$status] }}"></span>{{ $label }}</div>
                    <div class="font-display text-xl mt-0.5">{{ $pipeline[$status] ?? 0 }}</div>
                </a>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div class="space-y-5 min-w-0">
            {{-- My day --}}
            <section class="card">
                <div class="card-pad pb-3 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="card-title">{{ $isManager ? "Team's day" : 'My day' }}</h2>
                        <p class="text-xs text-muted">
                            @if ($overdueCount)<span class="text-red-600 font-bold">{{ $overdueCount }} overdue</span> · @endif
                            {{ $myDay->count() - $overdueCount }} due today
                        </p>
                    </div>
                    <a href="{{ route('admin.crm.follow-ups.index') }}" class="text-xs font-bold text-muted hover:text-ink">All follow-ups</a>
                </div>
                @if ($myDay->isEmpty())
                    <x-admin.empty icon="check" title="Nothing due today" text="When you log a call, schedule the next follow-up and it will show up here." class="py-8" />
                @else
                    <ul class="divide-y divide-line border-t border-line">
                        @foreach ($myDay as $f)
                            @php $overdue = $f->isOverdue(); @endphp
                            <li class="px-5 py-3 flex items-center gap-3">
                                <div @class(['w-16 shrink-0 text-center rounded-lg py-1.5', 'bg-red-50 text-red-600' => $overdue, 'bg-canvas' => ! $overdue]) title="{{ $f->due_at->format('D j M, g:i A') }}">
                                    <div class="text-[10px] font-bold">{{ $f->due_at->isToday() ? 'Today' : $f->due_at->format('j M') }}</div>
                                    <div class="text-xs font-bold">{{ $f->due_at->format('g:i A') }}</div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.crm.leads.show', $f->lead) }}" class="font-bold hover:text-brand truncate block">{{ $f->lead->parent_name }}</a>
                                    <div class="text-xs text-muted truncate">
                                        {{ $f->typeLabel() }}@if ($f->lead->child_name) · {{ $f->lead->child_name }}@endif
                                        @if ($isManager) · {{ $f->user?->name ?? 'Unassigned' }}@endif
                                        @if ($f->notes) · {{ $f->notes }}@endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-admin.crm.contact-buttons :lead="$f->lead" />
                                    <button type="button" class="btn btn-secondary btn-sm size-9 sm:size-8 px-0 text-lime" aria-label="Complete"
                                            @click="$dispatch('complete-follow-up', { url: @js(route('admin.crm.follow-ups.complete', $f)), title: @js($f->typeLabel().' '.$f->lead->parent_name) })">
                                        <x-icon name="check" class="size-4" />
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Recent leads --}}
            <section class="card">
                <div class="card-pad pb-3 flex items-center justify-between">
                    <h2 class="card-title">Recent leads</h2>
                    <a href="{{ route('admin.crm.leads.index', ['view' => 'all']) }}" class="text-xs font-bold text-muted hover:text-ink">View all</a>
                </div>
                @if ($recent->isEmpty())
                    <x-admin.empty icon="users" title="No leads yet" text="Website enquiries appear here as soon as a parent sends the form." class="py-8" />
                @else
                    <ul class="divide-y divide-line border-t border-line">
                        @foreach ($recent as $lead)
                            <li>
                                <a href="{{ route('admin.crm.leads.show', $lead) }}" class="px-5 py-3 flex items-center gap-3 hover:bg-canvas/60">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold truncate">{{ $lead->parent_name }}</div>
                                        <div class="text-xs text-muted truncate">
                                            {{ $lead->reference }}@if ($lead->child_name) · {{ $lead->child_name }}@endif @if ($lead->classroom) · {{ $lead->classroom->name }}@endif · {{ $lead->branch?->short_name ?: $lead->branch?->name }}
                                        </div>
                                    </div>
                                    <x-admin.crm.status-badge :status="$lead->status" class="hidden sm:inline-flex" />
                                    <span class="text-xs text-muted whitespace-nowrap"><x-admin.crm.when :date="$lead->created_at" short /></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="space-y-5 min-w-0">
            @if ($isManager)
                <section class="card">
                    <div class="card-pad pb-3">
                        <h2 class="card-title">New &amp; unassigned</h2>
                        <p class="text-xs text-muted">Give these families an agent.</p>
                    </div>
                    @if ($unassigned->isEmpty())
                        <p class="px-5 pb-5 text-sm text-muted">Every open lead has an agent.</p>
                    @else
                        <ul class="divide-y divide-line border-t border-line">
                            @foreach ($unassigned as $lead)
                                <li class="px-5 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="font-bold hover:text-brand truncate">{{ $lead->parent_name }}</a>
                                        <span class="text-xs text-muted whitespace-nowrap"><x-admin.crm.when :date="$lead->created_at" short /></span>
                                    </div>
                                    <div class="text-xs text-muted">{{ $lead->branch?->short_name ?: $lead->branch?->name ?: 'No branch' }}@if ($lead->classroom) · {{ $lead->classroom->name }}@endif</div>
                                    <form method="POST" action="{{ route('admin.crm.leads.assign', $lead) }}" class="mt-2">
                                        @csrf @method('PATCH')
                                        <select name="assigned_to" class="input h-9" onchange="this.form.submit()" aria-label="Assign {{ $lead->parent_name }}">
                                            <option value="">Assign to…</option>
                                            @foreach ($agents as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                                        </select>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card card-pad">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="card-title">Website today</h2>
                        <a href="{{ route('admin.analytics.overview') }}" class="text-xs font-bold text-muted hover:text-ink">Analytics</a>
                    </div>
                    <dl class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-canvas p-3"><dt class="stat-label">Visits today</dt><dd class="font-display text-2xl mt-1">{{ $website['visits_today'] }}</dd></div>
                        <div class="rounded-xl bg-canvas p-3"><dt class="stat-label">Website leads today</dt><dd class="font-display text-2xl mt-1">{{ $website['leads_today'] }}</dd></div>
                        <div class="rounded-xl bg-canvas p-3"><dt class="stat-label">Visitors, 7 days</dt><dd class="font-display text-2xl mt-1">{{ $website['visitors_7d'] }}</dd></div>
                        <div class="rounded-xl bg-canvas p-3"><dt class="stat-label">Top source, 7 days</dt><dd class="font-bold mt-1.5 truncate">{{ $website['top_source'] ?? '—' }}</dd></div>
                    </dl>
                </section>
            @endif
        </div>
    </div>

    <x-admin.crm.complete-modal />
@endsection
