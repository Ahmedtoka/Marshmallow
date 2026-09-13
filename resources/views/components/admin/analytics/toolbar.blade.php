@props(['range', 'filters' => ['source', 'device', 'campaign'], 'campaigns' => [], 'extra' => []])
{{--
    Analytics sub-navigation + date range bar + filters. Everything lives in the query string,
    so tabs keep the chosen range/filters. $extra: [name => value] hidden fields to keep (e.g. sort, segment).
--}}
@php
    use App\Models\Visit;
    use App\Support\Analytics\DateRange;
    use App\Support\Analytics\Filters;

    $keep = request()->only(['range', 'from', 'to', 'source', 'device', 'campaign']);
    $tabs = [
        ['Overview', 'admin.analytics.overview', 'chart', 'admin.analytics.overview'],
        ['Visitors', 'admin.analytics.visitors.index', 'eye', 'admin.analytics.visitors.*'],
        ['Sources', 'admin.analytics.sources', 'share', 'admin.analytics.sources'],
        ['Pages', 'admin.analytics.pages', 'file', 'admin.analytics.pages'],
        ['Actions', 'admin.analytics.actions', 'pointer', 'admin.analytics.actions'],
        ['Funnel', 'admin.analytics.funnel', 'funnel', 'admin.analytics.funnel'],
    ];
    $activeFilters = count(array_filter(request()->only($filters)));
@endphp

<div class="mb-6 space-y-3">
    <nav class="-mx-4 px-4 lg:mx-0 lg:px-0 overflow-x-auto" aria-label="Analytics sections">
        <div class="inline-flex gap-1 rounded-2xl bg-white border border-line p-1 min-w-max">
            @foreach ($tabs as [$label, $route, $icon, $pattern])
                @php $active = request()->routeIs($pattern); @endphp
                <a href="{{ route($route, $keep) }}" @class([
                    'inline-flex items-center gap-2 rounded-xl px-3 h-9 text-sm font-bold transition-colors',
                    'bg-ink text-white' => $active,
                    'text-muted hover:text-ink hover:bg-canvas' => ! $active,
                ]) @if ($active) aria-current="page" @endif>
                    <x-icon :name="$icon" class="size-4" /> {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    <form method="GET" class="card p-3 flex flex-wrap items-end gap-2"
          x-data="{ range: @js($range->key), more: {{ $activeFilters ? 'true' : 'false' }} }">
        @foreach ($extra as $name => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="w-full sm:w-auto">
            <label class="label" for="an-range">Date range</label>
            <select id="an-range" name="range" class="input sm:w-44" x-model="range"
                    @change="if (range !== 'custom') $el.form.submit()">
                @foreach (DateRange::PRESETS as $key => $label)
                    <option value="{{ $key }}" @selected($range->key === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2 w-full sm:w-auto" x-show="range === 'custom'" x-cloak>
            <div class="flex-1">
                <label class="label" for="an-from">From</label>
                <input id="an-from" type="date" name="from" class="input" value="{{ $range->from->toDateString() }}" max="{{ now()->toDateString() }}" :disabled="range !== 'custom'">
            </div>
            <div class="flex-1">
                <label class="label" for="an-to">To</label>
                <input id="an-to" type="date" name="to" class="input" value="{{ $range->to->toDateString() }}" max="{{ now()->toDateString() }}" :disabled="range !== 'custom'">
            </div>
        </div>

        @if ($filters)
            <button type="button" class="btn btn-secondary sm:hidden" @click="more = !more">
                <x-icon name="funnel" class="size-4" /> Filters @if ($activeFilters) <span class="badge badge-pink">{{ $activeFilters }}</span> @endif
            </button>

            <div class="w-full sm:w-auto sm:contents" :class="more ? '' : 'max-sm:hidden'">
                <div class="grid grid-cols-2 gap-2 sm:contents">
                    @if (in_array('source', $filters))
                        <div class="sm:w-40">
                            <label class="label" for="an-source">Source</label>
                            <select id="an-source" name="source" class="input">
                                <option value="">All sources</option>
                                @foreach (Visit::SOURCES as $key => $label)
                                    <option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if (in_array('device', $filters))
                        <div class="sm:w-36">
                            <label class="label" for="an-device">Device</label>
                            <select id="an-device" name="device" class="input">
                                <option value="">All devices</option>
                                @foreach (Filters::DEVICES as $key => $label)
                                    <option value="{{ $key }}" @selected(request('device') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if (in_array('campaign', $filters) && $campaigns)
                        <div class="col-span-2 sm:w-44">
                            <label class="label" for="an-campaign">Campaign</label>
                            <select id="an-campaign" name="campaign" class="input">
                                <option value="">All campaigns</option>
                                @foreach ($campaigns as $key => $label)
                                    <option value="{{ $key }}" @selected(request('campaign') === (string) $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{ $slot }}

        <div class="flex gap-2 w-full sm:w-auto">
            <button class="btn btn-primary flex-1 sm:flex-none">Apply</button>
            @if ($activeFilters || $range->key !== DateRange::DEFAULT)
                <a href="{{ url()->current() }}" class="btn btn-ghost">Reset</a>
            @endif
        </div>

        <p class="w-full text-xs text-muted tabular-nums">
            <x-icon name="calendar" class="size-3.5 inline -mt-0.5" /> {{ $range->label() }}
            <span class="hidden sm:inline">· compared with {{ $range->previous()->label() }}</span>
        </p>
    </form>
</div>
