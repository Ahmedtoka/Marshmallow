@extends('layouts.admin')
@section('title', $faq->exists ? 'Edit question' : 'Add question')

@section('content')
    <x-admin.page-header :title="$faq->exists ? 'Edit question' : 'Add question'" subtitle="Answer the way you would on the phone: short, warm and clear." :back="route('admin.content.faqs.index')">
        @if ($faq->exists)
            <x-slot:actions>
                <x-admin.confirm-delete :action="route('admin.content.faqs.destroy', $faq)" size="" message="Delete this question?" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $faq->exists ? route('admin.content.faqs.update', $faq) : route('admin.content.faqs.store') }}">
        @csrf
        @if ($faq->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card card-pad min-w-0 space-y-5 self-start lg:col-span-2">
                <x-admin.input name="question" label="Question" :value="$faq->question" required placeholder="What ages do you accept?" />
                <x-admin.textarea name="answer" label="Answer" :value="$faq->answer" rows="6" required />
            </div>
            <div class="card card-pad min-w-0 space-y-4 self-start">
                <h2 class="card-title">Publishing</h2>
                <x-admin.input name="category" label="Category" :value="$faq->category" list="faq-categories" hint="Pick one or type a new one (lowercase, no spaces)." />
                <datalist id="faq-categories">
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </datalist>
                <x-admin.toggle name="is_visible" label="Visible" :checked="$faq->is_visible" />
                <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$faq->sort_order" hint="Lower numbers show first." />
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $faq->exists ? 'Save question' : 'Add question', 'cancel' => route('admin.content.faqs.index')])
    </form>
@endsection
