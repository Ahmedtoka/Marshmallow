@php
    $creating = ! $lead->exists;
    $dob = old('child_dob', $lead->child_dob?->format('Y-m-d'));
@endphp

@if (session('duplicates'))
    <div class="mb-5 rounded-2xl border border-honey/40 bg-honey/10 p-4">
        <div class="font-bold flex items-center gap-2"><x-icon name="alert" class="size-5 text-honey" /> This phone number is already on file</div>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach (session('duplicates') as $d)
                <li>
                    @if ($d['url'])<a href="{{ $d['url'] }}" target="_blank" class="font-bold underline">{{ $d['reference'] }}</a>@else<b>{{ $d['reference'] }}</b>@endif
                    — {{ $d['parent_name'] }} · {{ $d['status'] }} · {{ $d['assignee'] ?? 'Unassigned' }} · added {{ $d['created'] }}
                </li>
            @endforeach
        </ul>
        <p class="text-sm text-muted mt-2">Open the existing lead to add a note there, or save this one anyway (e.g. a sibling).</p>
        <button name="allow_duplicate" value="1" class="btn btn-secondary btn-sm mt-3">Save anyway</button>
    </div>
@endif

<div x-data="leadForm(@js($finder), @js($dob), @js(old('academic_year', $lead->academic_year)), @js(old('interest', $lead->interest)))" class="grid gap-5 lg:grid-cols-3 lg:items-start">
    <div class="lg:col-span-2 space-y-5">
        <section class="card card-pad">
            <h2 class="card-title mb-4">Parent</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="parent_name" label="Parent name" :value="$lead->parent_name" required class="sm:col-span-2" autocomplete="off" />
                <x-admin.input name="phone" label="Mobile" type="tel" :value="$lead->phone" required placeholder="01012345678" inputmode="tel" hint="Egyptian mobile, any format — we'll tidy it up." />
                <x-admin.input name="whatsapp" label="WhatsApp (if different)" type="tel" :value="$lead->whatsapp" placeholder="01012345678" inputmode="tel" />
                <x-admin.input name="email" label="Email" type="email" :value="$lead->email" class="sm:col-span-2" />
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="card-title mb-4">Child</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.input name="child_name" label="Child name" :value="$lead->child_name" />
                <x-admin.input name="child_dob" label="Birthday" type="date" x-model="dob" max="{{ now()->toDateString() }}" />
                <x-admin.select name="academic_year" label="Academic year" :options="array_combine($years, $years)" x-model="year" />
            </div>
            <div class="mt-4 rounded-xl bg-canvas px-4 py-3 text-sm" x-show="dob" x-cloak>
                <template x-if="result && result.match">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-muted">Class:</span>
                        <span class="badge" :style="`background:${result.match.color}22;color:color-mix(in srgb, ${result.match.color} 60%, #26244F)`" x-text="result.match.name"></span>
                        <span class="text-muted" x-text="'· ' + ageLabel(result.months) + ' at the start of ' + year"></span>
                    </div>
                </template>
                <template x-if="result && !result.match && result.tooYoung">
                    <div class="text-honey font-bold">Too young for this year (<span x-text="ageLabel(result.months)"></span>) — the lead will be marked as waitlist.</div>
                </template>
                <template x-if="result && !result.match && !result.tooYoung">
                    <div class="text-muted">No class fits <span x-text="ageLabel(result.months)"></span> — past nursery age.</div>
                </template>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="card-title mb-4">Enquiry</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.select name="interest" label="Interested in" :options="\App\Models\Lead::INTERESTS" :value="$lead->interest" required x-model="interest" />
                <div x-show="interest === 'camp'" x-cloak>
                    <x-admin.select name="camp_id" label="Camp" :options="$camps" :value="$lead->camp_id" placeholder="Choose a camp" />
                </div>
                <x-admin.input name="preferred_tour_at" label="Preferred tour date" type="datetime-local" :value="$lead->preferred_tour_at?->format('Y-m-d\TH:i')" />
                <x-admin.input name="heard_from" label="Heard about us from" :value="$lead->heard_from" placeholder="e.g. A friend, Facebook" />
                <x-admin.textarea name="message" label="Message / notes from the parent" :value="$lead->message" rows="3" class="sm:col-span-2" />
            </div>
        </section>
    </div>

    <div class="space-y-5">
        <section class="card card-pad space-y-4">
            <h2 class="card-title">Handling</h2>
            <x-admin.select name="branch_id" label="Branch" :options="$branches" :value="$lead->branch_id" required placeholder="Choose a branch" />
            <x-admin.select name="channel" label="How they reached us" :options="$channels" :value="$lead->channel" required />
            <x-admin.select name="priority" label="Priority" :options="\App\Models\Lead::PRIORITIES" :value="$lead->priority" required />
            @if ($isManager)
                <x-admin.select name="assigned_to" label="Assigned to" :options="$agents" :value="$lead->assigned_to"
                    :placeholder="$creating ? 'Auto-assign by branch' : 'Unassigned'"
                    :hint="$creating ? 'Leave empty to give it to the branch agent with the fewest open leads.' : null" />
            @else
                <div>
                    <span class="label">Assigned to</span>
                    <p class="text-sm">{{ $creating ? 'You' : ($lead->assignee?->name ?? 'Unassigned') }}</p>
                </div>
            @endif
        </section>
        <div class="flex gap-2">
            <button class="btn btn-primary flex-1">{{ $creating ? 'Save lead' : 'Save changes' }}</button>
            <a href="{{ $creating ? route('admin.crm.leads.index') : route('admin.crm.leads.show', $lead) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function leadForm(cfg, dob, year, interest) {
        return {
            cfg,
            dob: dob || '',
            year: year || cfg.years[0],
            interest: interest || 'enrollment',
            get result() {
                if (!this.dob) return null;
                const d = new Date(this.dob + 'T00:00:00');
                if (isNaN(d)) return null;
                const start = parseInt(String(this.year).slice(0, 4), 10) || new Date().getFullYear();
                let ref = new Date(start, cfg.cutoff.month - 1, cfg.cutoff.day);
                const today = new Date(); today.setHours(0, 0, 0, 0);
                if (ref < today) ref = today;
                let months = (ref.getFullYear() - d.getFullYear()) * 12 + (ref.getMonth() - d.getMonth());
                if (ref.getDate() < d.getDate()) months--;
                months = Math.max(0, months);
                const match = cfg.classes.find((c) => months >= c.min && (c.max === null || months < c.max)) || null;
                const youngest = [...cfg.classes].sort((a, b) => a.min - b.min)[0];
                return { months, match, tooYoung: !match && !!youngest && months < youngest.min };
            },
            ageLabel(m) {
                const y = Math.floor(m / 12), mo = m % 12;
                return ((y ? y + 'y ' : '') + (mo ? mo + 'm' : '')).trim() || '0m';
            },
        };
    }
</script>
@endpush
