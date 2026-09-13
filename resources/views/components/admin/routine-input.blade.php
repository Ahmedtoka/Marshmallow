@props(['name', 'label' => null, 'items' => [], 'hint' => null])
{{-- Daily routine repeater, submitted as {name}[n][time] and {name}[n][label]. --}}
@php
    $values = collect(old($name, $items ?? []))->values()
        ->map(fn ($r, $i) => ['id' => $i, 'time' => (string) ($r['time'] ?? ''), 'label' => (string) ($r['label'] ?? '')])->all();
@endphp
<div {{ $attributes->class([]) }} x-data="{ rows: @js($values), next: {{ count($values) }},
    add() { this.rows.push({ id: this.next++, time: '', label: '' }); this.$nextTick(() => { const inputs = this.$root.querySelectorAll('input[data-time]'); inputs[inputs.length - 1]?.focus(); }); },
    move(i, d) { const j = i + d; if (j < 0 || j >= this.rows.length) return; [this.rows[i], this.rows[j]] = [this.rows[j], this.rows[i]]; } }">
    @if ($label)
        <span class="label">{{ $label }}</span>
    @endif
    <div x-show="rows.length" class="mb-1.5 hidden grid-cols-[6rem_1fr_4.5rem] gap-2 px-0.5 text-xs font-bold text-muted sm:grid">
        <span>Time</span><span>What happens</span><span></span>
    </div>
    <ul class="space-y-2">
        <template x-for="(row, index) in rows" :key="row.id">
            <li class="grid grid-cols-[5.5rem_1fr_auto] items-center gap-2 sm:grid-cols-[6rem_1fr_4.5rem]">
                <input type="text" data-time :name="`{{ $name }}[${row.id}][time]`" x-model="row.time" placeholder="9:00" maxlength="20" class="input">
                <input type="text" :name="`{{ $name }}[${row.id}][label]`" x-model="row.label" placeholder="Circle time" maxlength="255" class="input"
                    @keydown.enter.prevent="add()">
                <div class="flex">
                    <button type="button" class="btn btn-ghost btn-sm px-1.5" @click="move(index, -1)" :disabled="index === 0" title="Move up" aria-label="Move up">
                        <x-icon name="chevron-down" class="size-4 rotate-180" />
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm px-1.5 hover:text-red-600" @click="rows.splice(index, 1)" title="Remove" aria-label="Remove">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            </li>
        </template>
    </ul>
    <p x-show="rows.length === 0" class="rounded-xl border border-dashed border-line px-3 py-3 text-sm text-muted">No routine yet.</p>
    <button type="button" class="btn btn-secondary btn-sm mt-2" @click="add()">
        <x-icon name="plus" class="size-4" /> Add a time slot
    </button>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @if ($errors->has($name.'.*'))
        <p class="error">{{ $errors->first($name.'.*') }}</p>
    @endif
</div>
