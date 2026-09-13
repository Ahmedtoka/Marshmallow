@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])
@php $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name)); $key = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label class="label" for="{{ $id }}">{{ $label }} @if ($required)<span class="text-brand">*</span>@endif</label>
    @endif
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($key, $value) }}" @required($required)
        {{ $attributes->except('class')->class(['input', 'input-error' => $errors->has($key)]) }}>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($key) <p class="error">{{ $message }}</p> @enderror
</div>
