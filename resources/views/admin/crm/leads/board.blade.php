@extends('layouts.admin')
@section('title', 'Pipeline board')
@section('content')
    <x-admin.page-header title="Pipeline board" subtitle="Drag a card to move the family to the next stage.">
        <x-slot:actions>
            <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary"><x-icon name="users" class="size-4" /> <span class="hidden sm:inline">List</span></a>
            <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add lead</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <select name="branch" class="input h-9 w-auto" onchange="this.form.submit()" aria-label="Branch">
            <option value="">All branches</option>
            @foreach ($options['branches'] as $k => $v)<option value="{{ $k }}" @selected(request('branch') == $k)>{{ $v }}</option>@endforeach
        </select>
        @if ($isManager)
            <select name="assignee" class="input h-9 w-auto" onchange="this.form.submit()" aria-label="Agent">
                <option value="">Everyone</option>
                <option value="none" @selected(request('assignee') === 'none')>Unassigned</option>
                @foreach ($options['agents'] as $k => $v)<option value="{{ $k }}" @selected(request('assignee') == $k)>{{ $v }}</option>@endforeach
            </select>
        @endif
        <select name="interest" class="input h-9 w-auto" onchange="this.form.submit()" aria-label="Interest">
            <option value="">Any interest</option>
            @foreach (\App\Models\Lead::INTERESTS as $k => $v)<option value="{{ $k }}" @selected(request('interest') === $k)>{{ $v }}</option>@endforeach
        </select>
        @if (request()->hasAny(['branch', 'assignee', 'interest']))
            <a href="{{ route('admin.crm.leads.board') }}" class="btn btn-ghost btn-sm h-9">Clear</a>
        @endif
        <noscript><button class="btn btn-secondary btn-sm h-9">Apply</button></noscript>
    </form>

    <div x-data="leadBoard(@js(collect($columns)->map(fn ($c) => $c['count'])))" class="-mx-4 px-4 lg:mx-0 lg:px-0 overflow-x-auto pb-4">
        <div class="flex gap-3 items-start min-w-max">
            @foreach ($columns as $status => $column)
                <section class="w-[272px] shrink-0 rounded-2xl bg-white/70 border border-line flex flex-col max-h-[calc(100vh-230px)] min-h-40">
                    <header class="px-3 pt-2.5 pb-2 rounded-t-2xl border-t-4" style="border-top-color: {{ $column['color'] }}">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="font-display text-[15px] font-medium">{{ $column['label'] }}</h2>
                            <span class="rounded-full px-2 text-xs font-bold leading-5" style="background: {{ $column['color'] }}22; color: color-mix(in srgb, {{ $column['color'] }} 60%, #26244F)" x-text="counts[@js($status)]">{{ $column['count'] }}</span>
                        </div>
                    </header>
                    <div class="flex-1 overflow-y-auto px-2 pb-2 space-y-2 min-h-24" data-column data-status="{{ $status }}">
                        @foreach ($column['leads'] as $lead)
                            <article class="card p-3 cursor-grab active:cursor-grabbing select-none" data-id="{{ $lead->id }}" data-url="{{ route('admin.crm.leads.status', $lead) }}">
                                <div class="flex items-start justify-between gap-2">
                                    <a href="{{ route('admin.crm.leads.show', $lead) }}" class="font-bold leading-snug hover:text-brand">{{ $lead->parent_name }}</a>
                                    <x-admin.crm.avatar :user="$lead->assignee" size="size-6" />
                                </div>
                                @if ($lead->child_name || $lead->classroom)
                                    <div class="mt-1 flex items-center gap-1.5 text-xs">
                                        @if ($lead->classroom)<span class="size-2 rounded-full shrink-0" style="background: {{ $lead->classroom->color }}" title="{{ $lead->classroom->name }}"></span>@endif
                                        <span class="truncate">{{ $lead->child_name }}{{ $lead->child_name && $lead->classroom ? ' · ' : '' }}{{ $lead->classroom?->name }}</span>
                                    </div>
                                @endif
                                <div class="mt-2 flex items-center justify-between gap-2 text-[11px] text-muted">
                                    <span class="truncate">{{ $lead->branch?->short_name ?: $lead->branch?->name }}</span>
                                    <span title="{{ $lead->created_at->format('j M Y, g:i A') }}">{{ (int) floor($lead->created_at->diffInDays(now())) }}d</span>
                                </div>
                                @if ($lead->next_follow_up_at)
                                    <div @class(['mt-1.5 text-[11px] font-bold flex items-center gap-1', 'text-red-600' => $lead->isOverdue(), 'text-ink' => ! $lead->isOverdue()])>
                                        <x-icon name="clock" class="size-3" />
                                        <span title="{{ $lead->next_follow_up_at->format('D j M, g:i A') }}">{{ $lead->isOverdue() ? 'Overdue · ' : '' }}{{ $lead->next_follow_up_at->diffForHumans(short: true) }}</span>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    @if ($column['count'] > $column['leads']->count())
                        <a href="{{ route('admin.crm.leads.index', array_filter(['view' => 'all', 'status' => $status, 'branch' => request('branch'), 'assignee' => request('assignee'), 'interest' => request('interest')])) }}"
                           class="block text-center text-xs font-bold text-muted hover:text-ink py-2 border-t border-line">View all {{ $column['count'] }}</a>
                    @endif
                </section>
            @endforeach
        </div>

        {{-- Lost reason --}}
        <div x-show="lost.open" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-ink/40 sm:p-4" @keydown.escape.window="lost.open && cancelLost()">
            <form class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-5 space-y-4" @submit.prevent="confirmLost()">
                <div>
                    <h2 class="card-title">Why is the family not interested?</h2>
                    <p class="text-sm text-muted">This helps the team see where we lose families.</p>
                </div>
                <div>
                    <label class="label" for="lost-reason">Reason <span class="text-brand">*</span></label>
                    <select id="lost-reason" class="input" x-model="lost.reason" required>
                        <option value="">Choose a reason</option>
                        @foreach (\App\Models\Lead::LOST_REASONS as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="lost-note">Note</label>
                    <textarea id="lost-note" class="input" rows="2" x-model="lost.note" placeholder="Optional"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn btn-ghost" @click="cancelLost()">Cancel</button>
                    <button class="btn btn-primary" :disabled="!lost.reason">Save</button>
                </div>
            </form>
        </div>

        <div x-show="toast" x-cloak x-transition class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 rounded-xl bg-ink text-white px-4 py-2.5 text-sm font-semibold shadow-lg" x-text="toast"></div>
    </div>
@endsection

@push('scripts')
<script>
    function leadBoard(counts) {
        return {
            counts,
            toast: '',
            lost: { open: false, reason: '', note: '', item: null, revert: null },
            init() {
                this.$el.querySelectorAll('[data-column]').forEach((column) => {
                    window.Sortable.create(column, {
                        group: 'leads',
                        animation: 150,
                        delay: 180,
                        delayOnTouchOnly: true,
                        ghostClass: 'opacity-40',
                        onEnd: (evt) => this.moved(evt),
                    });
                });
            },
            moved(evt) {
                if (evt.from === evt.to) return;
                const from = evt.from.dataset.status, to = evt.to.dataset.status, item = evt.item;
                this.counts[from]--; this.counts[to]++;
                const revert = () => {
                    evt.from.insertBefore(item, evt.from.children[evt.oldIndex] || null);
                    this.counts[from]++; this.counts[to]--;
                };
                if (to === 'lost') {
                    this.lost = { open: true, reason: '', note: '', item, revert };
                    return;
                }
                this.send(item, { status: to }, revert);
            },
            cancelLost() {
                this.lost.revert && this.lost.revert();
                this.lost = { open: false, reason: '', note: '', item: null, revert: null };
            },
            confirmLost() {
                if (!this.lost.reason) return;
                this.send(this.lost.item, { status: 'lost', lost_reason: this.lost.reason, note: this.lost.note || null }, this.lost.revert);
                this.lost = { open: false, reason: '', note: '', item: null, revert: null };
            },
            send(item, payload, revert) {
                window.csrfFetch(item.dataset.url, { method: 'PATCH', body: JSON.stringify(payload) })
                    .then((r) => r.ok ? r.json() : Promise.reject(r))
                    .then((data) => this.flash('Moved to ' + data.label))
                    .catch(() => { revert(); this.flash('Could not move the lead. Please try again.'); });
            },
            flash(message) {
                this.toast = message;
                clearTimeout(this._t);
                this._t = setTimeout(() => this.toast = '', 2500);
            },
        };
    }
</script>
@endpush
