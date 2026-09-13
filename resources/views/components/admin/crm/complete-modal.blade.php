{{-- Open with: $dispatch('complete-follow-up', { url: '...', title: '...' }) --}}
<div x-data="{ open: false, url: '', title: '', schedule: false }"
     @complete-follow-up.window="open = true; url = $event.detail.url; title = $event.detail.title; schedule = false; $nextTick(() => $refs.outcome.focus())"
     @keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-ink/40 sm:p-4">
    <form method="POST" :action="url" @click.outside="open = false"
          class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-5 space-y-4 max-h-[90vh] overflow-y-auto">
        @csrf
        @method('PATCH')
        <div>
            <h2 class="card-title">Complete follow-up</h2>
            <p class="text-sm text-muted" x-text="title"></p>
        </div>
        <div>
            <label class="label" for="cm-outcome">What happened?</label>
            <textarea id="cm-outcome" x-ref="outcome" name="outcome" rows="3" class="input" placeholder="e.g. Spoke to mum, she wants to visit on Saturday"></textarea>
        </div>
        <label class="flex items-center gap-2 font-bold">
            <input type="checkbox" name="next[schedule]" value="1" class="checkbox" x-model="schedule"> Schedule the next follow-up
        </label>
        <div x-show="schedule" x-cloak class="grid grid-cols-2 gap-3">
            <div>
                <label class="label" for="cm-type">Type</label>
                <select id="cm-type" name="next[type]" class="input">
                    @foreach (\App\Models\FollowUp::TYPES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="cm-due">When</label>
                <input id="cm-due" type="datetime-local" name="next[due_at]" class="input" value="{{ now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i') }}" :required="schedule">
            </div>
            <div class="col-span-2">
                <label class="label" for="cm-notes">Notes</label>
                <input id="cm-notes" name="next[notes]" class="input" placeholder="Optional">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" class="btn btn-ghost" @click="open = false">Cancel</button>
            <button class="btn btn-success"><x-icon name="check" class="size-4" /> Mark done</button>
        </div>
    </form>
</div>
