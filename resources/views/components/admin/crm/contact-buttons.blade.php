@props(['lead', 'size' => 'sm'])
@php
    $agent = \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ');
    $text = 'Hello '.$lead->parent_name.', this is '.$agent.' from Marshmallow Nursery. Thank you for your interest'
        .($lead->child_name ? ' in a place for '.$lead->child_name : '').'. When would be a good time to talk?';
@endphp
@if ($size === 'lg')
    <a href="{{ tel_link($lead->phone) }}" class="btn btn-primary h-12 text-base flex-1"><x-icon name="phone" class="size-5" /> Call</a>
    <a href="{{ $lead->whatsappLink($text) }}" target="_blank" rel="noopener" class="btn h-12 text-base flex-1 text-white hover:brightness-95" style="background:#25D366"><x-icon name="whatsapp" class="size-5" /> WhatsApp</a>
@else
    <a href="{{ tel_link($lead->phone) }}" class="btn btn-secondary btn-sm size-9 sm:size-8 px-0" title="Call {{ $lead->phone }}" aria-label="Call {{ $lead->parent_name }}"><x-icon name="phone" class="size-4" /></a>
    <a href="{{ $lead->whatsappLink($text) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm size-9 sm:size-8 px-0 text-[#1DA851]" title="WhatsApp" aria-label="WhatsApp {{ $lead->parent_name }}"><x-icon name="whatsapp" class="size-4" /></a>
@endif
