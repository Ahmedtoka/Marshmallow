@extends('layouts.admin')
@section('title', 'Visitor journey')

@php
    use App\Models\Visit;
    use App\Support\Analytics\Format;
    use App\Support\Analytics\Journey;
    use App\Support\Analytics\PageName;

    $lead = $visitor->lead;
    $title = $lead?->parent_name ?? 'Visitor #'.$visitor->id;
@endphp

@section('content')
    <x-admin.page-header :title="$title" :back="route('admin.analytics.visitors.index')"
        subtitle="Visitor since {{ $visitor->first_seen_at->format('j M Y, g:i a') }} · last seen {{ $visitor->last_seen_at->diffForHumans() }}" />

    <div class="grid gap-5 lg:grid-cols-3 mb-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <x-admin.analytics.kpi label="Visits" :value="Format::num($visitor->visits_count)" />
                <x-admin.analytics.kpi label="Pageviews" :value="Format::num($pageviews)" />
                <x-admin.analytics.kpi label="Engaged time" :value="Format::duration($engaged)" hint="Visible-tab time" />
                <x-admin.analytics.kpi label="Actions" :value="Format::num($eventCount)" hint="Taps, clicks, sections" />
            </div>

            <div class="card card-pad">
                <h2 class="card-title mb-3">How they found us</h2>
                <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="stat-label">First source</dt>
                        <dd class="font-bold">{{ Visit::SOURCES[$visitor->first_source] ?? ucfirst((string) $visitor->first_source) ?: '—' }}
                            @if ($visitor->first_referrer_host)<span class="text-muted font-normal">· {{ $visitor->first_referrer_host }}</span>@endif
                        </dd>
                    </div>
                    <div>
                        <dt class="stat-label">Campaign</dt>
                        <dd class="font-bold">{{ collect([$visitor->first_utm_source, $visitor->first_utm_campaign])->filter()->implode(' · ') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="stat-label">Landing page</dt>
                        <dd class="font-bold">{{ PageName::for($visitor->first_landing_path) }} <span class="text-muted font-normal">{{ $visitor->first_landing_path }}</span></dd>
                    </div>
                    <div>
                        <dt class="stat-label">Device</dt>
                        <dd><x-admin.analytics.device class="text-ink font-bold" :device="$visitor->device_type" :browser="$visitor->browser" :os="$visitor->os" /></dd>
                    </div>
                    @if ($visitor->country)
                        <div>
                            <dt class="stat-label">Country</dt>
                            <dd class="font-bold">{{ $visitor->country }}</dd>
                        </div>
                    @endif
                    @if ($firstVisit?->language)
                        <div>
                            <dt class="stat-label">Browser language</dt>
                            <dd class="font-bold">{{ $firstVisit->language }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($badges)
                    <div class="mt-4 pt-4 border-t border-line">
                        <p class="stat-label mb-2">Key behaviour</p>
                        <x-admin.analytics.badges :badges="$badges" />
                    </div>
                @endif
            </div>
        </div>

        <aside>
            @if ($lead)
                <div class="card card-pad border-brand/30">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <h2 class="card-title">Linked lead</h2>
                        <span class="badge" style="background: {{ $lead->statusColor() }}1F; color: {{ $lead->statusColor() }}">{{ $lead->statusLabel() }}</span>
                    </div>
                    <p class="font-display text-xl">{{ $lead->parent_name }}</p>
                    <p class="text-sm text-muted tabular-nums">{{ $lead->reference }} · {{ $lead->phone }}</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        @if ($lead->child_name)
                            <div class="flex justify-between gap-3"><dt class="text-muted">Child</dt><dd class="font-bold text-right">{{ $lead->child_name }}@if ($lead->childAgeLabel()) <span class="text-muted font-normal">({{ $lead->childAgeLabel() }})</span>@endif</dd></div>
                        @endif
                        @if ($lead->classroom)
                            <div class="flex justify-between gap-3"><dt class="text-muted">Class</dt><dd class="font-bold flex items-center gap-1.5"><span class="size-2 rounded-full" style="background: {{ $lead->classroom->color }}"></span>{{ $lead->classroom->name }}</dd></div>
                        @endif
                        @if ($lead->branch)
                            <div class="flex justify-between gap-3"><dt class="text-muted">Branch</dt><dd class="font-bold">{{ $lead->branch->name }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3"><dt class="text-muted">Assigned to</dt><dd class="font-bold">{{ $lead->assignee?->name ?? 'Unassigned' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Submitted</dt><dd class="font-bold tabular-nums">{{ $lead->created_at->format('j M Y, g:i a') }}</dd></div>
                    </dl>
                    @if ($lead->trashed())
                        <p class="mt-4 text-sm text-muted">This lead was deleted.</p>
                    @else
                        <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-primary w-full mt-4">Open lead <x-icon name="arrow-right" class="size-4" /></a>
                    @endif
                </div>
            @else
                <div class="card card-pad">
                    <h2 class="card-title mb-1">Not a lead yet</h2>
                    <p class="text-sm text-muted">This visitor hasn't submitted the enrollment form from this browser.</p>
                </div>
            @endif
        </aside>
    </div>

    <div class="flex flex-wrap items-end justify-between gap-2 mb-3">
        <h2 class="font-display text-xl">Journey</h2>
        <p class="text-sm text-muted">Newest visit first · {{ Format::num($visits->total()) }} {{ Str::plural('visit', $visits->total()) }}</p>
    </div>

    @if ($visits->isEmpty())
        <div class="card"><x-admin.empty icon="eye" title="No visits recorded" /></div>
    @endif

    <div class="space-y-4">
        @foreach ($visits as $visit)
            @php
                $items = Journey::timeline($visit, $lead);
                $number = $visits->total() - (($visits->currentPage() - 1) * $visits->perPage()) - $loop->index;
            @endphp
            <section @class(['card overflow-hidden', 'border-brand/40' => $visit->converted])>
                <header @class(['px-4 py-3 border-b border-line', 'bg-brand-soft/60' => $visit->converted, 'bg-canvas/60' => ! $visit->converted])>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-display text-[17px]">
                            Visit {{ $number }} · <span class="tabular-nums">{{ $visit->started_at->format('D j M Y, g:i a') }}</span>
                        </h3>
                        <div class="flex flex-wrap gap-1.5">
                            @if ($visit->converted)<span class="badge badge-pink"><x-icon name="star" class="size-3" /> Became lead</span>@endif
                            @if ($visit->is_bounce)<span class="badge badge-muted">Bounced</span>@endif
                        </div>
                    </div>
                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted tabular-nums">
                        <span><x-icon name="share" class="size-3 inline -mt-0.5" /> {{ Journey::visitSource($visit) }}@if ($visit->utm_medium) ({{ $visit->utm_medium }})@endif</span>
                        <span><x-icon name="door" class="size-3 inline -mt-0.5" /> Landed on {{ PageName::for($visit->landing_path) }}</span>
                        <span><x-icon name="clock" class="size-3 inline -mt-0.5" /> {{ Format::humanDuration($visit->duration_seconds) }}</span>
                        <span><x-icon name="file" class="size-3 inline -mt-0.5" /> {{ $visit->pageviews }} {{ Str::plural('page', $visit->pageviews) }}</span>
                        <x-admin.analytics.device compact :device="$visit->device_type" :browser="$visit->browser" :os="$visit->os" />
                    </div>
                </header>
                <div class="px-4 py-4">
                    @if (! $items)
                        <p class="text-sm text-muted">No pages recorded for this visit.</p>
                    @else
                        <ol class="relative before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-px before:bg-line">
                            @foreach ($items as $item)
                                <x-admin.analytics.timeline-item :item="$item" />
                            @endforeach
                        </ol>
                    @endif
                </div>
            </section>
        @endforeach
    </div>

    @if ($visits->hasPages())
        <div class="card px-4 py-3 mt-4">{{ $visits->links('admin.partials.pagination') }}</div>
    @endif
@endsection
