@props(['action', 'message' => 'Delete this item? This can’t be undone.', 'label' => 'Delete', 'icon' => false, 'size' => 'btn-sm'])
{{-- A DELETE form that asks for confirmation first. --}}
<form method="POST" action="{{ $action }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from($message) }})" {{ $attributes->class(['inline-flex']) }}>
    @csrf
    @method('DELETE')
    @if ($icon)
        <button type="submit" class="btn btn-ghost {{ $size }} px-2 hover:bg-red-50 hover:text-red-600" title="{{ $label }}" aria-label="{{ $label }}">
            <x-icon name="trash" class="size-4" />
        </button>
    @else
        <button type="submit" class="btn btn-danger {{ $size }}">
            <x-icon name="trash" class="size-4" /> {{ $label }}
        </button>
    @endif
</form>
