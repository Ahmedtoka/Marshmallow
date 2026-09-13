{{--
    Compact website journey for the CRM lead page.
    Usage: @include('admin.analytics.partials.journey', ['visitor' => $lead->visitor])
    Receives $visitor (App\Models\Visitor|null). Needs no layout variables; runs ~5 small queries.
--}}
@php
    use App\Models\Visit;
    use App\Support\Analytics\Format;
    use App\Support\Analytics\Journey;
    use App\Support\Analytics\PageName;
    use Illuminate\Support\Facades\DB;

    $visitor = $visitor ?? null;
    $journeyMaxItems = 15;

    if ($visitor) {
        $journeyLead = $visitor->relationLoaded('lead') ? $visitor->lead : $visitor->lead()->first();
        $journeyVisits = $visitor->visits()->with(['pageViews', 'events'])->limit(3)->get();
        $journeyEngaged = (int) DB::table('page_views')->where('visitor_id', $visitor->id)->sum('duration_seconds');
        $journeyBadges = Journey::badges(Journey::summaries([$visitor->id])->get($visitor->id));
        $journeyCanOpen = (bool) auth()->user()?->hasRole('admin', 'sales_manager');
    }
@endphp

<div class="mm-journey space-y-4">
    @if (! $visitor)
        <p class="text-sm text-muted">No website activity recorded — this lead was added manually, or the parent's browser blocked cookies.</p>
    @else
        <div class="flex flex-wrap items-start justify-between gap-3">
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-5 gap-y-2 text-sm flex-1 min-w-0">
                <div class="min-w-0">
                    <dt class="text-xs text-muted font-semibold">First source</dt>
                    <dd class="font-bold truncate">{{ Visit::SOURCES[$visitor->first_source] ?? ucfirst((string) $visitor->first_source) ?: '—' }}@if ($visitor->first_utm_campaign)<span class="font-normal text-muted"> · {{ $visitor->first_utm_campaign }}</span>@endif</dd>
                </div>
                <div class="min-w-0">
                    <dt class="text-xs text-muted font-semibold">Landing page</dt>
                    <dd class="font-bold truncate" title="{{ $visitor->first_landing_path }}">{{ PageName::for($visitor->first_landing_path) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted font-semibold">Visits</dt>
                    <dd class="font-bold tabular-nums">{{ Format::num($visitor->visits_count) }} <span class="font-normal text-muted">· {{ Format::num($visitor->pageviews_count) }} pages</span></dd>
                </div>
                <div>
                    <dt class="text-xs text-muted font-semibold">Engaged time</dt>
                    <dd class="font-bold tabular-nums">{{ Format::humanDuration($journeyEngaged) }}</dd>
                </div>
            </dl>
            @if ($journeyCanOpen)
                <a href="{{ route('admin.analytics.visitors.show', $visitor) }}" class="btn btn-secondary btn-sm">Full journey <x-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>

        <x-admin.analytics.badges :badges="$journeyBadges" />

        @foreach ($journeyVisits as $visit)
            @php
                $all = Journey::timeline($visit, $journeyLead);
                $shown = array_slice($all, 0, $journeyMaxItems);
                $more = count($all) - count($shown);
            @endphp
            <div class="rounded-xl border border-line">
                <div @class(['px-3 py-2 border-b border-line rounded-t-xl', 'bg-brand-soft/60' => $visit->converted, 'bg-canvas/60' => ! $visit->converted])>
                    <p class="text-[13px] font-bold tabular-nums">{{ $visit->started_at->format('D j M, g:i a') }}
                        @if ($visit->converted)<span class="badge badge-pink ml-1">Became lead</span>@endif
                    </p>
                    <p class="text-xs text-muted">{{ Journey::visitSource($visit) }} · {{ $visit->pageviews }} {{ Str::plural('page', $visit->pageviews) }} · {{ Format::humanDuration($visit->duration_seconds) }}</p>
                </div>
                <ol class="relative px-3 py-3 before:absolute before:left-6 before:top-4 before:bottom-4 before:w-px before:bg-line">
                    @forelse ($shown as $item)
                        <x-admin.analytics.timeline-item :item="$item" compact />
                    @empty
                        <li class="text-xs text-muted">No pages recorded.</li>
                    @endforelse
                </ol>
                @if ($more > 0)
                    <p class="px-3 pb-2 text-xs text-muted font-semibold">+{{ $more }} more</p>
                @endif
            </div>
        @endforeach

        @if ($visitor->visits_count > 3)
            <p class="text-xs text-muted">Showing the latest 3 of {{ $visitor->visits_count }} visits.</p>
        @endif
    @endif
</div>
