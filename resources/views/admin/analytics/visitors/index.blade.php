@extends('layouts.admin')
@section('title', 'Visitors')

@php
    use App\Http\Controllers\Admin\Analytics\VisitorController;
    use App\Models\Visit;
    use App\Support\Analytics\Format;
    use App\Support\Analytics\Journey;
    use App\Support\Analytics\PageName;
@endphp

@section('content')
    <x-admin.page-header title="Visitors" subtitle="Everyone who visited in this date range, with what they did." />

    <x-admin.analytics.toolbar :range="$range" :filters="['source', 'device']">
        <div class="w-full sm:w-52">
            <label class="label" for="an-segment">Show</label>
            <select id="an-segment" name="segment" class="input">
                @foreach (VisitorController::SEGMENTS as $key => $label)
                    <option value="{{ $key }}" @selected($segment === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-60">
            <label class="label" for="an-q">Find a lead's visitor</label>
            <input id="an-q" type="search" name="q" value="{{ $search }}" class="input" placeholder="Reference, phone or name">
        </div>
    </x-admin.analytics.toolbar>

    <div class="card overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-line">
            <p class="text-sm text-muted"><b class="text-ink tabular-nums">{{ Format::num($visitors->total()) }}</b> {{ Str::plural('visitor', $visitors->total()) }} · {{ VisitorController::SEGMENTS[$segment] }}</p>
            <p class="text-xs text-muted">Visits, pages and time are lifetime totals</p>
        </div>

        @if ($visitors->isEmpty())
            <x-admin.empty icon="eye" title="No visitors match" text="Try a longer date range, another segment, or clear the search." />
        @else
            {{-- Desktop table --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th>First seen</th>
                            <th>Last seen</th>
                            <th class="text-right">Visits</th>
                            <th class="text-right">Pages</th>
                            <th class="text-right">Time</th>
                            <th>First source &amp; device</th>
                            <th>Key behaviour</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visitors as $visitor)
                            @php $badges = Journey::badges($summaries->get($visitor->id), $visitor->lead); @endphp
                            <tr>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.analytics.visitors.show', $visitor) }}" class="font-bold hover:text-brand">
                                        {{ $visitor->lead?->parent_name ?? 'Visitor #'.$visitor->id }}
                                    </a>
                                    <div class="text-xs text-muted truncate max-w-48">{{ PageName::for($visitor->first_landing_path) }}</div>
                                </td>
                                <td class="whitespace-nowrap text-muted tabular-nums">{{ $visitor->first_seen_at->format('j M, g:i a') }}</td>
                                <td class="whitespace-nowrap tabular-nums" title="{{ $visitor->last_seen_at->format('j M Y, g:i a') }}">{{ $visitor->last_seen_at->diffForHumans(short: true) }}</td>
                                <td class="text-right tabular-nums">{{ Format::num($visitor->visits_count) }}</td>
                                <td class="text-right tabular-nums">{{ Format::num($visitor->pageviews_count) }}</td>
                                <td class="text-right tabular-nums">{{ Format::duration($visitor->engaged_seconds) }}</td>
                                <td class="max-w-56">
                                    <div class="font-semibold truncate">{{ Visit::SOURCES[$visitor->first_source] ?? ucfirst((string) $visitor->first_source) ?: '—' }}@if ($visitor->first_utm_campaign)<span class="text-xs text-muted font-normal"> · {{ $visitor->first_utm_campaign }}</span>@endif</div>
                                    <x-admin.analytics.device class="text-xs" compact :device="$visitor->device_type" :browser="$visitor->browser" :os="$visitor->os" />
                                </td>
                                <td class="min-w-56"><x-admin.analytics.badges :badges="$badges" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Phone / tablet stacked rows --}}
            <ul class="lg:hidden divide-y divide-line">
                @foreach ($visitors as $visitor)
                    @php $badges = Journey::badges($summaries->get($visitor->id), $visitor->lead); @endphp
                    <li class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('admin.analytics.visitors.show', $visitor) }}" class="font-bold hover:text-brand">{{ $visitor->lead?->parent_name ?? 'Visitor #'.$visitor->id }}</a>
                                <p class="text-xs text-muted truncate">
                                    {{ Visit::SOURCES[$visitor->first_source] ?? ucfirst((string) $visitor->first_source) }} · {{ PageName::for($visitor->first_landing_path) }}
                                </p>
                            </div>
                            <span class="text-xs text-muted whitespace-nowrap tabular-nums">{{ $visitor->last_seen_at->diffForHumans(short: true) }}</span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted tabular-nums">
                            <span><b class="text-ink">{{ $visitor->visits_count }}</b> {{ Str::plural('visit', $visitor->visits_count) }}</span>
                            <span><b class="text-ink">{{ $visitor->pageviews_count }}</b> pages</span>
                            <span><b class="text-ink">{{ Format::duration($visitor->engaged_seconds) }}</b> time</span>
                            <x-admin.analytics.device compact :device="$visitor->device_type" :browser="$visitor->browser" :os="$visitor->os" />
                        </div>
                        <x-admin.analytics.badges class="mt-2" :badges="$badges" />
                    </li>
                @endforeach
            </ul>

            <div class="px-4 py-3 border-t border-line">
                {{ $visitors->links('admin.partials.pagination') }}
            </div>
        @endif
    </div>
@endsection
