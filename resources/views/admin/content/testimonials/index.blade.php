@extends('layouts.admin')
@section('title', 'Testimonials')

@section('content')
    <x-admin.page-header title="Testimonials" subtitle="Parent quotes shown on the homepage. Featured quotes show first.">
        <x-slot:actions>
            <a href="{{ route('admin.content.testimonials.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add testimonial</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($testimonials->isEmpty())
        <div class="card">
            <x-admin.empty icon="chat" title="No testimonials yet" text="Copy a kind review from Facebook or Google and add it here.">
                <a href="{{ route('admin.content.testimonials.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add testimonial</a>
            </x-admin.empty>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <article class="card card-pad flex flex-col">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <span class="text-honey" aria-label="{{ $testimonial->rating }} out of 5">{{ str_repeat('★', $testimonial->rating) }}<span class="text-line">{{ str_repeat('★', 5 - $testimonial->rating) }}</span></span>
                        <div class="flex gap-1">
                            @if ($testimonial->is_featured) <span class="badge badge-pink">Featured</span> @endif
                            @unless ($testimonial->is_visible) <span class="badge badge-muted">Hidden</span> @endunless
                        </div>
                    </div>
                    <p class="line-clamp-4 flex-1 text-ink/85">“{{ $testimonial->quote }}”</p>
                    <div class="mt-4 flex items-center gap-3 border-t border-line pt-4">
                        @if ($testimonial->photo)
                            <img src="{{ media_url($testimonial->photo) }}" alt="" class="size-9 shrink-0 rounded-full object-cover">
                        @else
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-grape/15 text-sm font-bold text-grape">{{ mb_strtoupper(mb_substr($testimonial->parent_name, 0, 1)) }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold">{{ $testimonial->parent_name }}</p>
                            <p class="truncate text-xs text-muted">{{ $testimonial->relation }}@if ($testimonial->branch) · {{ $testimonial->branch->name }} @endif</p>
                        </div>
                        <a href="{{ route('admin.content.testimonials.edit', $testimonial) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <x-admin.confirm-delete :action="route('admin.content.testimonials.destroy', $testimonial)" icon :message="'Delete the testimonial from '.$testimonial->parent_name.'?'" />
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
