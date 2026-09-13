@props(['name', 'label' => null, 'items' => [], 'hint' => null, 'placeholder' => '', 'addLabel' => 'Add item'])
{{-- Repeater of short text lines, submitted as {name}[]. --}}
@php
    $values = collect(old($name, $items ?? []))->filter(fn ($v) => $v !== null)->values()
        ->map(fn ($v, $i) => ['id' => $i, 'v' => (string) $v])->all();
@endphp
<div {{ $attributes->class([]) }} x-data="{ items: @js($values), next: {{ count($values) }},
    add() { this.items.push({ id: this.next++, v: '' }); this.$nextTick(() => { const inputs = this.$root.querySelectorAll('input[type=text]'); inputs[inputs.length - 1]?.focus(); }); },
    move(i, d) { const j = i + d; if (j < 0 || j >= this.items.length) return; [this.items[i], this.items[j]] = [this.items[j], this.items[i]]; } }">
    @if ($label)
        <span class="label">{{ $label }}</span>
    @endif
    <ul class="space-y-2">
        <template x-for="(item, index) in items" :key="item.id">
            <li class="flex items-center gap-1.5">
                <span class="w-5 shrink-0 text-right text-xs font-bold text-muted" x-text="index + 1"></span>
                <input type="text" name="{{ $name }}[]" x-model="item.v" placeholder="{{ $placeholder }}" maxlength="255" class="input"
                    @keydown.enter.prevent="add()">
                <button type="button" class="btn btn-ghost btn-sm shrink-0 px-1.5" @click="move(index, -1)" :disabled="index === 0" title="Move up" aria-label="Move up">
                    <x-icon name="chevron-down" class="size-4 rotate-180" />
                </button>
                <button type="button" class="btn btn-ghost btn-sm shrink-0 px-1.5 hover:text-red-600" @click="items.splice(index, 1)" title="Remove" aria-label="Remove">
                    <x-icon name="x" class="size-4" />
                </button>
            </li>
        </template>
    </ul>
    <p x-show="items.length === 0" class="rounded-xl border border-dashed border-line px-3 py-3 text-sm text-muted">Nothing added yet.</p>
    <button type="button" class="btn btn-secondary btn-sm mt-2" @click="add()">
        <x-icon name="plus" class="size-4" /> {{ $addLabel }}
    </button>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @foreach ($errors->get($name.'.*') as $messages)
        <p class="error">{{ $messages[0] }}</p>
    @endforeach
    @error($name) <p class="error">{{ $message }}</p> @enderror
</div>
