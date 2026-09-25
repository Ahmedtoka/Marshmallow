@extends('layouts.admin')
@section('title', $lead->reference.' · '.$lead->parent_name)
@section('content')
    @php
        use App\Models\Lead;
        use App\Models\FollowUp;
        use App\Models\LeadActivity;

        $steps = ['new', 'contacted', 'tour_booked', 'toured', 'enrolled'];
        $currentIndex = array_search($lead->status, $steps, true);
        $isLost = $lead->status === 'lost';
        $defaultDue = now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');
        $hasContact = $lead->activities->whereIn('type', ['call', 'whatsapp', 'email'])->isNotEmpty();
        $typeIcons = [
            'created' => 'sparkle', 'note' => 'note', 'call' => 'phone', 'whatsapp' => 'whatsapp', 'email' => 'mail',
            'status_changed' => 'arrow-right', 'assigned' => 'users', 'follow_up_scheduled' => 'calendar',
            'follow_up_done' => 'check', 'updated' => 'pencil',
        ];
    @endphp

    <a href="{{ route('admin.crm.leads.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-muted hover:text-ink mb-3"><x-icon name="arrow-left" class="size-4" /> Leads</a>

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-sm text-muted font-semibold">
                {{ $lead->reference }} · added <x-admin.crm.when :date="$lead->created_at" />
                · {{ $lead->channel === 'website' ? 'Website'.($sourceLabel ? ' ('.$sourceLabel.')' : '') : (Lead::CHANNELS[$lead->channel] ?? $lead->channel) }}
            </div>
            <h1 class="page-title mt-0.5 break-words">{{ $lead->parent_name }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-admin.crm.status-badge :status="$lead->status" />
                @if ($isLost && $lead->lost_reason)<span class="badge badge-muted">{{ $lead->lost_reason }}</span>@endif
                <form method="POST" action="{{ route('admin.crm.leads.update', $lead) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="quick_priority" value="1">
                    <label class="sr-only" for="priority">Priority</label>
                    <select id="priority" name="priority" onchange="this.form.submit()"
                            @class(['h-7 rounded-full border-0 pl-3 pr-7 text-xs font-bold cursor-pointer',
                                    'bg-red-50 text-red-600' => $lead->priority === 'hot',
                                    'bg-honey/15 text-[#94650B]' => $lead->priority === 'warm',
                                    'bg-teal/15 text-[#1B7F88]' => $lead->priority === 'cold'])>
                        @foreach (Lead::PRIORITIES as $k => $v)<option value="{{ $k }}" @selected($lead->priority === $k)>{{ $v }}</option>@endforeach
                    </select>
                </form>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($isManager)
                <form method="POST" action="{{ route('admin.crm.leads.assign', $lead) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <x-admin.crm.avatar :user="$lead->assignee" size="size-9" />
                    <label class="sr-only" for="assigned_to">Assigned to</label>
                    <select id="assigned_to" name="assigned_to" class="input h-10 w-auto min-w-40" onchange="this.form.submit()">
                        <option value="">Unassigned</option>
                        @foreach ($agents as $agent)<option value="{{ $agent->id }}" @selected((int) $lead->assigned_to === (int) $agent->id)>{{ $agent->name }}</option>@endforeach
                    </select>
                </form>
            @else
                <span class="flex items-center gap-2 text-sm font-semibold"><x-admin.crm.avatar :user="$lead->assignee" /> {{ $lead->assignee?->name ?? 'Unassigned' }}</span>
            @endif
            <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-secondary"><x-icon name="pencil" class="size-4" /> Edit</a>
            @if ($isManager)
                <form method="POST" action="{{ route('admin.crm.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead? It will disappear from lists and reports.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger px-3" aria-label="Delete lead" title="Delete lead"><x-icon name="trash" class="size-4" /></button>
                </form>
            @endif
        </div>
    </div>

    {{-- Status stepper --}}
    <div class="mt-4" x-data="{ lostOpen: {{ $errors->has('lost_reason') ? 'true' : 'false' }} }">
        <div class="-mx-4 px-4 lg:mx-0 lg:px-0 overflow-x-auto">
            <div class="flex gap-1.5 min-w-max sm:min-w-0">
                @foreach ($steps as $i => $step)
                    @php
                        $color = Lead::STATUS_COLORS[$step];
                        $isCurrent = $lead->status === $step;
                        $isPast = ! $isLost && $currentIndex !== false && $i < $currentIndex;
                    @endphp
                    <form method="POST" action="{{ route('admin.crm.leads.status', $lead) }}" class="flex-1 min-w-[112px]">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $step }}">
                        <button @disabled($isCurrent) aria-current="{{ $isCurrent ? 'step' : 'false' }}"
                                class="w-full h-11 rounded-xl px-2 text-[13px] font-bold flex items-center justify-center gap-1.5 transition disabled:cursor-default hover:brightness-95 border"
                                style="{{ $isCurrent ? "background: $color; color: #fff; border-color: $color" : ($isPast ? "background: {$color}1f; color: color-mix(in srgb, $color 60%, #26244F); border-color: transparent" : 'background: #fff; color: #6B6890; border-color: #E7E5F2') }}">
                            @if ($isPast)<x-icon name="check" class="size-4" />@endif
                            {{ Lead::STATUSES[$step] }}
                        </button>
                    </form>
                @endforeach
                <button type="button" @click="lostOpen = true" @disabled($isLost)
                        class="min-w-[112px] sm:ml-2 h-11 rounded-xl px-3 text-[13px] font-bold border transition hover:bg-canvas disabled:cursor-default"
                        style="{{ $isLost ? 'background:#9B98B8;color:#fff;border-color:#9B98B8' : 'background:#fff;color:#6B6890;border-color:#E7E5F2' }}">
                    Not interested
                </button>
            </div>
        </div>

        <div x-show="lostOpen" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-ink/40 sm:p-4" @keydown.escape.window="lostOpen = false">
            <form method="POST" action="{{ route('admin.crm.leads.status', $lead) }}" @click.outside="lostOpen = false" class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-5 space-y-4">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="lost">
                <div>
                    <h2 class="card-title">Mark as not interested</h2>
                    <p class="text-sm text-muted">Pending follow-ups will be closed.</p>
                </div>
                <div>
                    <label class="label" for="lost_reason">Reason <span class="text-brand">*</span></label>
                    <select id="lost_reason" name="lost_reason" class="input" required>
                        <option value="">Choose a reason</option>
                        @foreach (Lead::LOST_REASONS as $r)<option value="{{ $r }}" @selected(old('lost_reason') === $r)>{{ $r }}</option>@endforeach
                    </select>
                    @error('lost_reason')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="lost_note">Note</label>
                    <textarea id="lost_note" name="note" rows="2" class="input" placeholder="Optional"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn btn-ghost" @click="lostOpen = false">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Contact bar --}}
    <div class="card p-3 mt-4 flex flex-wrap items-center gap-2" x-data="{ copied: false }">
        <div class="flex gap-2 w-full sm:w-auto sm:flex-1">
            <x-admin.crm.contact-buttons :lead="$lead" size="lg" />
        </div>
        <button type="button" class="btn btn-secondary h-12 flex-1 sm:flex-none"
                @click="navigator.clipboard && navigator.clipboard.writeText(@js($lead->phone)); copied = true; setTimeout(() => copied = false, 1500)">
            <x-icon name="file" class="size-4" /> <span x-text="copied ? 'Copied' : @js($lead->phone)">{{ $lead->phone }}</span>
        </button>
        @if ($lead->email)
            <a href="mailto:{{ $lead->email }}" class="btn btn-secondary h-12 flex-1 sm:flex-none min-w-0"><x-icon name="mail" class="size-4" /> <span class="truncate">{{ $lead->email }}</span></a>
        @endif
        @if ($lead->whatsapp && $lead->whatsapp !== $lead->phone)
            <p class="w-full text-xs text-muted px-1">WhatsApp number: {{ $lead->whatsapp }}</p>
        @endif
    </div>

    @if ($duplicates->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-honey/40 bg-honey/10 px-4 py-3 text-sm">
            <b>Same phone on {{ $duplicates->count() === 1 ? 'another lead' : $duplicates->count().' other leads' }}:</b>
            @foreach ($duplicates as $d)
                @if ($d['url'])<a href="{{ $d['url'] }}" class="font-bold underline">{{ $d['reference'] }}</a>@else{{ $d['reference'] }}@endif ({{ $d['status'] }}){{ $loop->last ? '' : ',' }}
            @endforeach
        </div>
    @endif

    @if ($lead->status === 'new' && $hasContact)
        <div class="mt-4 rounded-2xl border border-grape/30 bg-grape/10 px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <span class="font-semibold">You've been in touch with this family. Move the lead on?</span>
            <form method="POST" action="{{ route('admin.crm.leads.status', $lead) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="contacted">
                <button class="btn btn-secondary btn-sm"><x-icon name="check" class="size-4" /> Mark as contacted</button>
            </form>
        </div>
    @endif

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px] lg:grid-rows-[auto_1fr] lg:items-start">
        {{-- Details (top right on desktop, first on mobile) --}}
        <section class="card lg:col-start-2 lg:row-start-1">
            <div class="card-pad pb-3 flex items-center justify-between">
                <h2 class="card-title">Details</h2>
                <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="text-xs font-bold text-muted hover:text-ink">Edit</a>
            </div>
            <dl class="px-5 pb-5 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2.5 text-sm">
                <dt class="text-muted">Child</dt><dd class="font-bold">{{ $lead->child_name ?: '—' }}</dd>
                <dt class="text-muted">Birthday</dt><dd>{{ $lead->child_dob?->format('j M Y') ?? '—' }}</dd>
                <dt class="text-muted">Age</dt><dd>{{ $lead->childAgeLabel() ? $lead->childAgeLabel().' at school-year start' : '—' }}</dd>
                <dt class="text-muted">Class</dt>
                <dd>@if ($lead->classroom)<x-admin.crm.class-chip :classroom="$lead->classroom" :href="route('classes.show', $lead->classroom)" />@else — @endif</dd>
                <dt class="text-muted">Year</dt><dd>{{ $lead->academic_year ?: '—' }}</dd>
                <dt class="text-muted">Branch</dt><dd>{{ $lead->branch?->name ?? '—' }}</dd>
                <dt class="text-muted">Interest</dt><dd>{{ Lead::INTERESTS[$lead->interest] ?? $lead->interest }}</dd>
                @if ($lead->interest === 'camp' || $lead->camp)
                    <dt class="text-muted">Camp</dt><dd>{{ $lead->camp?->title ?? '—' }}</dd>
                @endif
                <dt class="text-muted">Tour</dt><dd>{{ $lead->preferred_tour_at?->format('D j M Y, g:i A') ?? '—' }}</dd>
                <dt class="text-muted">Heard from</dt><dd>{{ $lead->heard_from ?: '—' }}</dd>
                <dt class="text-muted">Last contact</dt><dd><x-admin.crm.when :date="$lead->last_contacted_at" empty="Not yet" /></dd>
            </dl>
            @if ($lead->message)
                <div class="mx-5 mb-5 rounded-xl bg-canvas p-3 text-sm whitespace-pre-line"><span class="block text-xs font-bold text-muted mb-1">Parent's message</span>{{ $lead->message }}</div>
            @endif
        </section>

        {{-- Work area --}}
        <div class="space-y-5 lg:col-start-1 lg:row-start-1 lg:row-span-2 min-w-0">
            {{-- Composer --}}
            @php $oldType = old('type', 'call'); @endphp
            <form method="POST" action="{{ route('admin.crm.leads.activities.store', $lead) }}" class="card"
                  x-data="{ type: @js($oldType), schedule: {{ old('follow_up.schedule') ? 'true' : 'false' }} }">
                @csrf
                <input type="hidden" name="type" :value="type" value="{{ $oldType }}">
                <div class="flex border-b border-line px-2 overflow-x-auto">
                    @foreach (['call' => ['Call', 'phone'], 'whatsapp' => ['WhatsApp', 'whatsapp'], 'note' => ['Note', 'note'], 'email' => ['Email', 'mail']] as $k => [$label, $icon])
                        <button type="button" @click="type = @js($k)"
                                :class="type === @js($k) ? 'border-brand text-ink' : 'border-transparent text-muted hover:text-ink'"
                                class="flex items-center gap-1.5 px-3 h-11 -mb-px border-b-2 text-sm font-bold whitespace-nowrap">
                            <x-icon :name="$icon" class="size-4" /> {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="p-4 space-y-3">
                    <textarea name="body" rows="3" class="input @error('body') input-error @enderror"
                              :placeholder="({ call: 'How did the call go?', whatsapp: 'What did you send or receive?', note: 'Write a note for the team', email: 'What was the email about?' })[type]">{{ old('body') }}</textarea>
                    @error('body')<p class="error">{{ $message }}</p>@enderror

                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                        @if ($lead->status === 'new')
                            <label class="flex items-center gap-2 text-sm font-bold" x-show="type !== 'note'">
                                <input type="checkbox" name="mark_contacted" value="1" class="checkbox" checked> Mark as contacted
                            </label>
                        @endif
                        <label class="flex items-center gap-2 text-sm font-bold">
                            <input type="checkbox" name="follow_up[schedule]" value="1" class="checkbox" x-model="schedule"> Schedule next follow-up
                        </label>
                    </div>

                    <div x-show="schedule" x-cloak class="grid gap-3 sm:grid-cols-[140px_200px_1fr]">
                        <select name="follow_up[type]" class="input" aria-label="Follow-up type">
                            @foreach (FollowUp::TYPES as $k => $v)<option value="{{ $k }}" @selected(old('follow_up.type', 'call') === $k)>{{ $v }}</option>@endforeach
                        </select>
                        <input type="datetime-local" name="follow_up[due_at]" value="{{ old('follow_up.due_at', $defaultDue) }}" class="input" aria-label="When">
                        <input name="follow_up[notes]" value="{{ old('follow_up.notes') }}" class="input" placeholder="What to do (optional)">
                        @error('follow_up.due_at')<p class="error sm:col-span-3">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end">
                        <button class="btn btn-primary">Log activity</button>
                    </div>
                </div>
            </form>

            {{-- Follow-ups --}}
            <section class="card" x-data="{ adding: false }">
                <div class="card-pad pb-3 flex items-center justify-between gap-2">
                    <h2 class="card-title">Follow-ups</h2>
                    <button type="button" class="btn btn-ghost btn-sm" @click="adding = !adding"><x-icon name="plus" class="size-4" /> Add</button>
                </div>
                <form x-show="adding" x-cloak method="POST" action="{{ route('admin.crm.leads.follow-ups.store', $lead) }}" class="mx-5 mb-4 p-3 rounded-xl bg-canvas grid gap-2 sm:grid-cols-[140px_200px_1fr_auto]">
                    @csrf
                    <select name="type" class="input" aria-label="Type">
                        @foreach (FollowUp::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                    <input type="datetime-local" name="due_at" value="{{ $defaultDue }}" class="input" required aria-label="When">
                    <input name="notes" class="input" placeholder="Notes (optional)">
                    <button class="btn btn-primary">Save</button>
                </form>

                @if ($pendingFollowUps->isEmpty())
                    <p class="px-5 pb-5 text-sm text-muted">No follow-up planned. @if (! in_array($lead->status, ['enrolled', 'lost']))Schedule one so this family isn't forgotten.@endif</p>
                @else
                    <ul class="divide-y divide-line border-t border-line">
                        @foreach ($pendingFollowUps as $f)
                            <li class="px-5 py-3 flex flex-wrap items-center gap-3">
                                <span @class(['size-9 rounded-xl grid place-items-center shrink-0', 'bg-red-50 text-red-600' => $f->isOverdue(), 'bg-canvas text-ink' => ! $f->isOverdue()])>
                                    <x-icon :name="['call' => 'phone', 'whatsapp' => 'whatsapp', 'tour' => 'door', 'meeting' => 'users', 'email' => 'mail'][$f->type] ?? 'calendar'" class="size-4" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold">
                                        {{ $f->typeLabel() }}
                                        <span @class(['font-semibold text-sm', 'text-red-600' => $f->isOverdue(), 'text-muted' => ! $f->isOverdue()])>
                                            · {{ $f->due_at->format('D j M, g:i A') }} ({{ $f->isOverdue() ? 'overdue, ' : '' }}{{ $f->due_at->diffForHumans() }})
                                        </span>
                                    </div>
                                    @if ($f->notes)<div class="text-sm text-muted">{{ $f->notes }}</div>@endif
                                    @if ($f->user && $f->user_id !== auth()->id())<div class="text-xs text-muted">For {{ $f->user->name }}</div>@endif
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" class="btn btn-success btn-sm"
                                            @click="$dispatch('complete-follow-up', { url: @js(route('admin.crm.follow-ups.complete', $f)), title: @js($f->typeLabel().' · '.$f->due_at->format('D j M, g:i A')) })">
                                        <x-icon name="check" class="size-4" /> Complete
                                    </button>
                                    <form method="POST" action="{{ route('admin.crm.follow-ups.destroy', $f) }}" onsubmit="return confirm('Remove this follow-up?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm px-2" aria-label="Remove follow-up"><x-icon name="trash" class="size-4" /></button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($doneFollowUps->isNotEmpty())
                    <details class="border-t border-line">
                        <summary class="px-5 py-3 text-sm font-bold text-muted cursor-pointer hover:text-ink">Completed ({{ $doneFollowUps->count() }})</summary>
                        <ul class="px-5 pb-4 space-y-2">
                            @foreach ($doneFollowUps as $f)
                                <li class="text-sm">
                                    <span class="font-bold">{{ $f->typeLabel() }}</span>
                                    <span class="text-muted">· due {{ $f->due_at->format('j M, g:i A') }} · done <x-admin.crm.when :date="$f->completed_at" /></span>
                                    @if ($f->outcome)<div class="text-muted">{{ $f->outcome }}</div>@endif
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </section>

            {{-- Timeline --}}
            <section class="card card-pad">
                <h2 class="card-title mb-4">Timeline</h2>
                <ol class="relative space-y-5 before:absolute before:left-[17px] before:top-2 before:bottom-2 before:w-px before:bg-line">
                    @foreach ($lead->activities as $a)
                        <li class="relative flex gap-3">
                            <span class="relative z-[1] size-9 rounded-full bg-white border border-line grid place-items-center shrink-0 text-muted"
                                  @if ($a->type === 'status_changed' && isset($a->meta['to'])) style="border-color: {{ Lead::STATUS_COLORS[$a->meta['to']] ?? '#E7E5F2' }}; color: {{ Lead::STATUS_COLORS[$a->meta['to']] ?? '#6B6890' }}" @endif>
                                <x-icon :name="$typeIcons[$a->type] ?? 'note'" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1 pt-1">
                                <div class="text-sm">
                                    <span class="font-bold">{{ $a->typeLabel() }}</span>
                                    <span class="text-muted">· {{ $a->user?->name ?? 'System' }} · <x-admin.crm.when :date="$a->created_at" /></span>
                                </div>
                                @if ($a->type === 'status_changed')
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        @if (! empty($a->meta['from']))<x-admin.crm.status-badge :status="$a->meta['from']" /> <x-icon name="arrow-right" class="size-3.5 text-muted" />@endif
                                        @if (! empty($a->meta['to']))<x-admin.crm.status-badge :status="$a->meta['to']" />@endif
                                        @if (! empty($a->meta['lost_reason']))<span class="badge badge-muted">{{ $a->meta['lost_reason'] }}</span>@endif
                                    </div>
                                @elseif ($a->type === 'follow_up_scheduled' && ! empty($a->meta['due_at']))
                                    <div class="text-sm text-muted mt-0.5">{{ FollowUp::TYPES[$a->meta['type'] ?? ''] ?? 'Follow-up' }} on {{ \Illuminate\Support\Carbon::parse($a->meta['due_at'])->format('D j M, g:i A') }}</div>
                                @endif
                                @if ($a->body)
                                    <div class="mt-1 text-sm whitespace-pre-line break-words {{ in_array($a->type, ['note', 'call', 'whatsapp', 'email', 'follow_up_done']) ? 'rounded-xl bg-canvas px-3 py-2' : 'text-muted' }}">{{ $a->body }}</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        {{-- Attribution + journey --}}
        <div class="space-y-5 lg:col-start-2 lg:row-start-2 min-w-0">
            <section class="card">
                <h2 class="card-title card-pad pb-3">Where they came from</h2>
                <dl class="px-5 pb-5 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2.5 text-sm">
                    <dt class="text-muted">Channel</dt><dd>{{ Lead::CHANNELS[$lead->channel] ?? $lead->channel }}</dd>
                    <dt class="text-muted">Source</dt><dd>{{ $sourceLabel ?? '—' }}</dd>
                    @foreach (['utm_source' => 'UTM source', 'utm_medium' => 'UTM medium', 'utm_campaign' => 'Campaign'] as $k => $label)
                        @if ($lead->$k)<dt class="text-muted">{{ $label }}</dt><dd class="break-all">{{ $lead->$k }}</dd>@endif
                    @endforeach
                    @if ($lead->landing_path)<dt class="text-muted">Landing page</dt><dd class="break-all">{{ $lead->landing_path }}</dd>@endif
                    @if ($lead->form_path)<dt class="text-muted">Form page</dt><dd class="break-all">{{ $lead->form_path }}</dd>@endif
                    @if ($lead->referrer)<dt class="text-muted">Referrer</dt><dd class="break-all">{{ \Illuminate\Support\Str::limit($lead->referrer, 80) }}</dd>@endif
                    <dt class="text-muted">Created</dt><dd>{{ $lead->created_at->format('D j M Y, g:i A') }}</dd>
                    @if ($lead->metaConversions->isNotEmpty())
                        <dt class="text-muted">Meta</dt>
                        <dd class="flex flex-wrap gap-1.5">
                            @foreach ($lead->metaConversions as $conversion)
                                <span class="badge {{ \App\Models\MetaConversion::STATUS_BADGES[$conversion->status] ?? 'badge-muted' }}" title="{{ $conversion->error }}">
                                    {{ $conversion->event_name }}: {{ \App\Models\MetaConversion::STATUSES[$conversion->status] ?? $conversion->status }}
                                </span>
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </section>

            <section class="card card-pad">
                <h2 class="card-title mb-3">Website journey</h2>
                @if ($lead->visitor && view()->exists('admin.analytics.partials.journey'))
                    @includeIf('admin.analytics.partials.journey', ['visitor' => $lead->visitor])
                @elseif ($lead->visitor)
                    <p class="text-sm text-muted">This family was tracked on the website ({{ $lead->visitor->visits_count }} visits, {{ $lead->visitor->pageviews_count }} pages).</p>
                @else
                    <p class="text-sm text-muted">No website visits are linked to this lead{{ $lead->channel !== 'website' ? ' — it was added by the team' : '' }}.</p>
                @endif
            </section>
        </div>
    </div>

    <x-admin.crm.complete-modal />
@endsection
