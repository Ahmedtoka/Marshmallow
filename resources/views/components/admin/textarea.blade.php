@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'rows' => 4, 'required' => false])
@php $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name)); $key = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label class="label" for="{{ $id }}">{{ $label }} @if ($required)<span class="text-brand">*</span>@endif</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
        {{ $attributes->except('class')->class(['input', 'input-error' => $errors->has($key)]) }}>{{ old($key, $value) }}</textarea>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($key) <p class="error">{{ $message }}</p> @enderror
</div>
