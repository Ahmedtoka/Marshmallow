@extends('layouts.admin')
@section('title', $branch->exists ? 'Edit '.$branch->name : 'Add branch')

@section('content')
    <x-admin.page-header :title="$branch->exists ? $branch->name : 'Add branch'" :subtitle="$branch->exists ? 'Shown on the Branches page, in the footer and in the enrollment form.' : 'A new location parents can visit.'" :back="route('admin.content.branches.index')">
        @if ($branch->exists)
            <x-slot:actions>
                <a href="{{ route('branches') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
                <x-admin.confirm-delete :action="route('admin.content.branches.destroy', $branch)" size="" :message="'Delete the '.$branch->name.' branch? This can’t be undone.'" />
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $branch->exists ? route('admin.content.branches.update', $branch) : route('admin.content.branches.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($branch->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Branch</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name" label="Name" :value="$branch->name" required placeholder="Hadayek Al Ahram" class="sm:col-span-2" />
                        <x-admin.input name="short_name" label="Short name" :value="$branch->short_name" placeholder="Hadayek" hint="Used on small buttons and in lead lists." />
                        <x-admin.input name="slug" label="Web address" :value="$branch->slug" placeholder="made from the name" hint="Leave empty to make it from the name." />
                    </div>
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Contact</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="phone" label="Phone" type="tel" :value="$branch->phone" required placeholder="01012666625" />
                        <x-admin.input name="whatsapp" label="WhatsApp" type="tel" :value="$branch->whatsapp" hint="Leave empty to use the phone number." />
                        <x-admin.input name="email" label="Email" type="email" :value="$branch->email" />
                        <x-admin.input name="working_hours" label="Working hours" :value="$branch->working_hours" placeholder="Sun – Thu, 7:00 am – 4:00 pm" />
                    </div>
                </div>

                <div class="card card-pad space-y-5">
                    <h2 class="card-title">Location</h2>
                    <x-admin.textarea name="address" label="Address" :value="$branch->address" rows="2" required />
                    <x-admin.input name="address_note" label="Landmark" :value="$branch->address_note" placeholder="Next to the District 10 mosque" hint="A short hint that helps parents find you." />
                    <x-admin.input name="map_url" label="Google Maps link" type="url" :value="$branch->map_url" placeholder="https://maps.app.goo.gl/…"
                        hint="In Google Maps, tap Share → Copy link. Used for the “Get directions” button." />
                    <x-admin.input name="map_embed_url" label="Map embed link" type="url" :value="$branch->map_embed_url"
                        hint="Leave empty to generate the map from the address automatically." />
                    @if ($branch->map_embed_url)
                        <div class="overflow-hidden rounded-xl border border-line">
                            <iframe src="{{ $branch->map_embed_url }}" class="block h-56 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map preview"></iframe>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="card card-pad space-y-4">
                    <h2 class="card-title">Publishing</h2>
                    <x-admin.toggle name="is_active" label="Active" :checked="$branch->is_active" hint="Inactive branches are hidden from the website and don’t receive new leads." />
                    <x-admin.input name="sort_order" label="Order" type="number" min="0" :value="$branch->sort_order" hint="Lower numbers show first." />
                </div>
                <div class="card card-pad">
                    <x-admin.image name="image" label="Photo" :path="$branch->image" hint="A photo of the building or entrance. Landscape, at least 1200 px wide." />
                </div>
            </div>
        </div>

        @include('admin.content.partials.save-bar', ['label' => $branch->exists ? 'Save changes' : 'Add branch', 'cancel' => route('admin.content.branches.index')])
    </form>
@endsection
