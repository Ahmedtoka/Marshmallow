{{-- Short "book a visit" form. Params: $branches, $place, optional $classroom --}}
@php $years = \App\Support\ClassFinder::academicYears(); @endphp
<form method="POST" action="{{ route('enroll.store') }}" data-track-form="enroll" novalidate class="grid gap-4">
    @csrf
    <input type="hidden" name="interest" value="tour">
    <input type="hidden" name="whatsapp_same" value="1">
    <input type="hidden" name="academic_year" value="{{ $years[0] }}">
    <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    @if ($errors->any())
        <p class="rounded-2xl bg-blush px-4 py-3 font-bold text-pink-600" role="alert">Please check the highlighted fields.</p>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="c-parent" class="field-label">Your name</label>
            <input id="c-parent" name="parent_name" value="{{ old('parent_name') }}" class="field-input" autocomplete="name" required @error('parent_name') aria-invalid="true" aria-describedby="c-parent-error" @enderror>
            @error('parent_name') <p id="c-parent-error" class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="c-phone" class="field-label">Mobile number</label>
            <input id="c-phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" placeholder="010 1234 5678" class="field-input" autocomplete="tel" required @error('phone') aria-invalid="true" aria-describedby="c-phone-error" @enderror>
            @error('phone') <p id="c-phone-error" class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="c-dob" class="field-label">Child’s birthday <span class="font-normal text-ink-muted">(optional)</span></label>
            <input id="c-dob" name="child_dob" type="date" value="{{ old('child_dob') }}" max="{{ now()->subDay()->toDateString() }}" class="field-input" @error('child_dob') aria-invalid="true" @enderror>
            @error('child_dob') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="c-branch" class="field-label">Branch</label>
            <select id="c-branch" name="branch_id" class="field-input" required @error('branch_id') aria-invalid="true" @enderror>
                <option value="">Choose a branch</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected((string) old('branch_id') === (string) $b->id || $branches->count() === 1)>{{ $b->name }}</option>
                @endforeach
            </select>
            @error('branch_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="btn btn-primary px-7" data-track="cta_click" data-track-label="{{ $place }} – Book a visit form">Book a visit</button>
        <a href="{{ route('enroll', array_filter(['class' => $classroom?->slug ?? null])) }}" class="link" data-track="cta_click" data-track-label="{{ $place }} – Full form">Add more details</a>
    </div>
</form>
