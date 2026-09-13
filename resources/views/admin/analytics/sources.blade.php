@extends('layouts.admin')
@section('title', 'Traffic sources')

@php
    use App\Models\Visit;
    use App\Support\Analytics\Format;
    use App\Support\Analytics\PageName;
@endphp

@section('content')
    <x-admin.page-header title="Traffic sources" subtitle="Where parents come from, and which sources turn into leads and enrollments." />

    <x-admin.analytics.toolbar :range="$range" :campaigns="$campaigns" />

    <section class="card overflow-hidden mb-5">
        <div class="px-5 pt-5 pb-3">
            <h2 class="card-title">Sources</h2>
            <p class="text-xs text-muted mt-0.5">Leads and enrolled are website leads created in this range, by the source of the visit that submitted the form</p>
        </div>
        @if ($sources->isEmpty())
            <x-admin.empty icon="share" title="No traffic in this date range" />
        @else
            <div class="overflow-x-auto">
                <table class="table tabular-nums">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th class="text-right">Visits</th>
                            <th class="text-right">Visitors</th>
                            <th class="text-right">Bounce rate</th>
                            <th class="text-right">Avg engaged</th>
                            <th class="text-right">Pages / visit</th>
                            <th class="text-right">Leads</th>
                            <th class="text-right">Enrolled</th>
                            <th class="text-right">Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $maxVisits = max(1, $sources->max('visits')); @endphp
                        @foreach ($sources as $row)
                            <tr>
                                <td class="min-w-40">
                                    <a href="{{ route('admin.analytics.sources', array_merge(request()->query(), ['source' => $row->source])) }}" class="font-bold hover:text-brand">{{ $row->label }}</a>
                                    <div class="mt-1 h-1.5 w-full max-w-40 rounded-full bg-canvas overflow-hidden"><div class="h-full bg-teal rounded-full" style="width: {{ $row->visits / $maxVisits * 100 }}%"></div></div>
                                </td>
                                <td class="text-right font-bold">{{ Format::num($row->visits) }}</td>
                                <td class="text-right">{{ Format::num($row->visitors) }}</td>
                                <td class="text-right">{{ Format::pct($row->bounce) }}</td>
                                <td class="text-right">{{ Format::duration($row->avg_engaged) }}</td>
                                <td class="text-right">{{ Format::num($row->ppv, 1) }}</td>
                                <td class="text-right font-bold text-brand-dark">{{ Format::num($row->leads) }}</td>
                                <td class="text-right font-bold text-[#557316]">{{ Format::num($row->enrolled) }}</td>
                                <td class="text-right">{{ $row->visits ? Format::pct($row->conversion, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php $tv = $sources->sum('visits'); $tl = $sources->sum('leads'); @endphp
                        <tr class="font-bold">
                            <td class="px-4 py-3 border-t border-line">Total</td>
                            <td class="px-4 py-3 border-t border-line text-right">{{ Format::num($tv) }}</td>
                            <td class="px-4 py-3 border-t border-line text-right text-muted font-normal" colspan="4">visitors can come from several sources</td>
                            <td class="px-4 py-3 border-t border-line text-right text-brand-dark">{{ Format::num($tl) }}</td>
                            <td class="px-4 py-3 border-t border-line text-right text-[#557316]">{{ Format::num($sources->sum('enrolled')) }}</td>
                            <td class="px-4 py-3 border-t border-line text-right">{{ Format::pct(Format::ratio($tl, $tv), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </section>

    <section class="card overflow-hidden mb-5">
        <div class="px-5 pt-5 pb-3">
            <h2 class="card-title">Campaigns</h2>
            <p class="text-xs text-muted mt-0.5">Visits tagged with UTM parameters (utm_source / utm_medium / utm_campaign)</p>
        </div>
        @if ($campaignRows->isEmpty())
            <p class="px-5 pb-5 text-sm text-muted">No tagged campaign visits in this range. Add <code class="text-xs bg-canvas px-1 rounded">?utm_source=facebook&amp;utm_campaign=…</code> to links in ads and posts to see them here.</p>
        @else
            <div class="overflow-x-auto">
                <table class="table tabular-nums">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Source</th>
                            <th>Medium</th>
                            <th class="text-right">Visits</th>
                            <th class="text-right">Visitors</th>
                            <th class="text-right">Leads</th>
                            <th class="text-right">Enrolled</th>
                            <th class="text-right">Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaignRows as $row)
                            <tr>
                                <td class="font-bold whitespace-nowrap">
                                    @if ($row->utm_campaign)
                                        <a href="{{ route('admin.analytics.sources', array_merge(request()->query(), ['campaign' => $row->utm_campaign])) }}" class="hover:text-brand">{{ $row->utm_campaign }}</a>
                                    @else
                                        <span class="text-muted font-normal">(no campaign)</span>
                                    @endif
                                </td>
                                <td>{{ $row->utm_source ?? '—' }}</td>
                                <td>{{ $row->utm_medium ?? '—' }}</td>
                                <td class="text-right font-bold">{{ Format::num($row->visits) }}</td>
                                <td class="text-right">{{ Format::num($row->visitors) }}</td>
                                <td class="text-right font-bold text-brand-dark">{{ Format::num($row->leads) }}</td>
                                <td class="text-right font-bold text-[#557316]">{{ Format::num($row->enrolled) }}</td>
                                <td class="text-right">{{ Format::pct($row->conversion, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="card card-pad">
            <h2 class="card-title">Top referring websites</h2>
            <p class="text-xs text-muted mt-0.5 mb-4">Visits that arrived from a link on another site (lead count in grey)</p>
            @if ($referrers->isEmpty())
                <p class="text-sm text-muted py-8 text-center">No referring websites in this range.</p>
            @else
                <x-admin.analytics.bars color="#8479BD" :rows="$referrers->map(fn ($r) => ['label' => $r->referrer_host, 'value' => $r->visits, 'sub' => $r->leads ? '· '.$r->leads.' '.Str::plural('lead', $r->leads) : null])" />
            @endif
        </section>

        <section class="card card-pad">
            <h2 class="card-title">Landing pages by source</h2>
            <p class="text-xs text-muted mt-0.5 mb-4">The first page parents saw, per source</p>
            @if ($landing->isEmpty())
                <p class="text-sm text-muted py-8 text-center">No visits in this range.</p>
            @else
                <div class="space-y-5">
                    @foreach ($landing->take(8) as $source => $rows)
                        <div>
                            <p class="text-xs font-bold text-muted mb-2">{{ Visit::SOURCES[$source] ?? ucfirst($source) }}</p>
                            <x-admin.analytics.bars color="#2CBCC9" :max="$landing->flatten()->max('visits')"
                                :rows="$rows->map(fn ($r) => ['label' => PageName::for($r->landing_path), 'value' => $r->visits, 'sub' => '· '.Format::pct($r->bounce, 0).' bounce'.($r->leads ? ' · '.$r->leads.' '.Str::plural('lead', (int) $r->leads) : '')])" />
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
