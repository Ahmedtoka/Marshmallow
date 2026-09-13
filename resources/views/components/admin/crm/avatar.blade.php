@props(['user' => null, 'size' => 'size-7'])
@if ($user)
    <span title="{{ $user->name }}" {{ $attributes->class([$size, 'rounded-full bg-grape/15 text-grape grid place-items-center text-[11px] font-bold shrink-0']) }}>{{ $user->initials() }}</span>
@else
    <span title="Unassigned" {{ $attributes->class([$size, 'rounded-full border border-dashed border-muted/50 text-muted grid place-items-center text-[11px] font-bold shrink-0']) }}>?</span>
@endif
