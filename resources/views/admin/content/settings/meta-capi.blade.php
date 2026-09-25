{{-- Settings → Tracking: is the Meta Conversions API working? The token itself is never shown. --}}
@php use App\Models\MetaConversion; @endphp

<section class="card" id="meta-capi">
    <div class="card-pad flex flex-wrap items-start justify-between gap-3 border-b border-line">
        <div>
            <h2 class="card-title">Meta Conversions API</h2>
            <p class="mt-0.5 max-w-2xl text-sm text-muted">
                Sends bookings, visit requests and job applications to Meta from the server as well, so ad blockers and iPhones
                don’t hide them from your ads. Meta counts each action once. The access token is kept in the server’s <code>.env</code> file, not here.
            </p>
        </div>
        @if (! $capi['enabled'])
            <span class="badge badge-muted">Off</span>
        @elseif ($capi['test_mode'])
            <span class="badge bg-honey/15 text-[#8A5D00]">Test mode</span>
        @else
            <span class="badge badge-green">On</span>
        @endif
    </div>

    <div class="card-pad space-y-4">
        {{-- Events are sent by the queue worker the cron starts, so the cron's health belongs here too. --}}
        @php $lastRun = \App\Support\Scheduler::lastRun(); @endphp
        <div id="scheduler" class="flex flex-wrap items-center gap-2 text-sm">
            <span class="font-bold">Background tasks (cron):</span>
            @if (\App\Support\Scheduler::isRunning())
                <span class="badge badge-green">Running</span>
                <span class="text-muted">last run {{ $lastRun->diffForHumans() }}</span>
            @else
                <span class="badge badge-red">Not running</span>
                <span class="text-muted">{{ $lastRun ? 'last run '.$lastRun->diffForHumans() : 'no run recorded yet' }}. Events wait until it runs: check Cloudways → Cron Job Management → Advanced.</span>
            @endif
        </div>

        @if (! $capi['enabled'])
            <p class="text-sm text-muted">
                @if (! $capi['has_pixel'])
                    Add the Meta Pixel ID above to switch it on.
                @else
                    Waiting for the access token: add <code>META_CAPI_TOKEN</code> to the server’s <code>.env</code> file, then run <code>php artisan config:cache</code>.
                @endif
            </p>
        @endif

        @if ($capi['test_mode'])
            <div class="flex gap-2 rounded-xl border border-honey/40 bg-honey/10 px-3 py-2.5 text-[13px] font-semibold text-[#8A5D00]">
                <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
                <span>Test mode: events show up only in Events Manager → Test Events and do not count for your ads. Remove <code>META_TEST_EVENT_CODE</code> from <code>.env</code> when you are done checking.</span>
            </div>
        @endif

        @if ($capi['stuck'])
            <div class="flex gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-[13px] font-semibold text-red-700">
                <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
                <span>{{ $capi['stuck'] }} {{ Str::plural('event', $capi['stuck']) }} waiting for more than 10 minutes. They are sent by the cron job every minute: check Cloudways → Cron Job Management.</span>
            </div>
        @endif

        @if ($capi['enabled'] || $capi['last'])
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <div class="stat-label">Sent in the last 24 hours</div>
                    <div class="stat-value mt-2">{{ $capi['sent_24h'] }}</div>
                </div>
                <div>
                    <div class="stat-label">Failed in the last 24 hours</div>
                    <div @class(['stat-value mt-2', 'text-red-600' => $capi['failed_24h'] > 0])>{{ $capi['failed_24h'] }}</div>
                </div>
                <div>
                    <div class="stat-label">Last sent</div>
                    <div class="mt-2 text-sm font-bold text-ink">
                        {{ $capi['last_sent']?->sent_at?->diffForHumans() ?? 'Nothing yet' }}
                    </div>
                </div>
            </div>
        @endif

        @if ($capi['last_error'])
            <div class="rounded-xl border border-line bg-canvas/60 px-3 py-2.5 text-sm">
                <p class="font-bold">Last error from Meta <span class="font-semibold text-muted">· {{ $capi['last_error']->updated_at->diffForHumans() }}, {{ $capi['last_error']->event_name }}</span></p>
                <p class="mt-1 break-words text-muted">{{ $capi['last_error']->error }}</p>
            </div>
        @endif
    </div>

    @if ($capi['recent']->isNotEmpty())
        <div class="overflow-x-auto border-t border-line">
            <table class="table">
                <thead>
                <tr>
                    <th>Event</th>
                    <th>For</th>
                    <th>Status</th>
                    <th>Tries</th>
                    <th>When</th>
                    <th>Details</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($capi['recent'] as $row)
                    <tr>
                        <td class="whitespace-nowrap font-bold">{{ $row->event_name }} @if ($row->test_event)<span class="badge badge-muted ml-1">Test</span>@endif</td>
                        <td class="whitespace-nowrap">
                            @if ($row->lead)
                                <a href="{{ route('admin.crm.leads.show', $row->lead) }}" class="font-bold hover:text-brand">{{ $row->lead->reference }}</a>
                            @elseif ($row->job_application_id)
                                Job application
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge {{ MetaConversion::STATUS_BADGES[$row->status] ?? 'badge-muted' }}">{{ MetaConversion::STATUSES[$row->status] ?? $row->status }}</span></td>
                        <td>{{ $row->attempts }}</td>
                        <td class="whitespace-nowrap text-muted">{{ $row->created_at->format('j M, g:i A') }}</td>
                        <td class="max-w-sm text-muted">
                            {{ $row->error ? Str::limit($row->error, 120) : ($row->status === 'sent' ? 'Received by Meta' : '') }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
