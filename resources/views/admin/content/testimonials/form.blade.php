@extends('layouts.admin')
@section('title', $testimonial->exists ? 'Edit testimonial' : 'Add testimonial')

@section('content')
    <x-admin.page-header :title="$testimonial->exists ? 'Testimonial from '.$testimonial->parent_name : 'Add testimonial'" subtitle="A quote from a parent, shown in the “In parents’ words” section of the homepage."
        :back="route('admin.content.testimonials.index')">
        @if ($testimonial->exists)
            <x-slot:actions>
                <x-admin.confirm-delete :action="route('admin.content.testimonials.destroy', $testimonial)" size="" :message="'Delete the testimonial from '.$testimonial->parent_name.'?'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $testimonial->exists ? route('admin.content.testimonials.update', $testimonial) : route('admin.content.testimonials.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($testimonial->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <x-admin.textarea name="quote" label="Quote" :value="$testimonial->quote" rows="6" required hint="Keep the parent’s own words. Shorter quotes read best on phones." />
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="parent_name" label="Parent’s name" :value="$testimonial->parent_name" required />
                        <x-admin.input name="relation" label="Relation" :value="$testimonial->relation" placeholder="Parent of a Cupcake child" />
                    </div>
                    <div x-data="{ rating: {{ (int) old('rating', $testimonial->rating ?: 5) }} }">
                        <span class="label">Rating</span>
                        <input type="hidden" name="rating" :value="rating">
                        <div class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" class="text-3xl leading-none transition-colors" :class="rating >= {{ $i }} ? 'text-honey' : 'text-line hover:text-honey/50'" aria-label="{{ $i }} stars">★</button>
                            @endfor
                        </div>
                        @error('rating') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <x-admin.input name="video_url" label="Video link" type="url" :value="$testimonial->video_url" placeholder="https://www.facebook.com/…" hint="Optional. A Facebook, YouTube or TikTok video of this parent." />
                    <x-admin.select name="branch_id" label="Branch" :options="$branches" :value="$testimonial->branch_id" placeholder="Not specific to a branch" />
                </div>
            </div>
            <div class="min-w-0 space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_visible" label="Visible" :checked="$testimonial->is_visible" />
                    <x-admin.toggle name="is_featured" label="Featured" :checked="$testimonial->is_featured" hint="Featured quotes show first." />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$testimonial->sort_order" hint="Lower numbers show first." />
                </div>
                <div class="card card-pad">
                    <x-admin.image name="photo" label="Photo" :path="$testimonial->photo" aspect="aspect-square max-w-40" hint="Optional. Only with the parent’s permission." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $testimonial->exists ? 'Save testimonial' : 'Add testimonial', 'cancel' => route('admin.content.testimonials.index')])
    </form>
@endsection
