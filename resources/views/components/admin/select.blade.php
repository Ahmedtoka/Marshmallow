@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false])
{{-- $options: [value => label]. --}}
@php $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name)); $key = str_replace(['[', ']'], ['.', ''], $name); $current = (string) old($key, $value); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label class="label" for="{{ $id }}">{{ $label }} @if ($required)<span class="text-brand">*</span>@endif</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" @required($required)
        {{ $attributes->except('class')->class(['input', 'input-error' => $errors->has($key)]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($key) <p class="error">{{ $message }}</p> @enderror
</div>
