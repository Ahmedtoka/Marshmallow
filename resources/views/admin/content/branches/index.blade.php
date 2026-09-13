@extends('layouts.admin')
@section('title', 'Branches')

@section('content')
    <x-admin.page-header title="Branches" subtitle="Addresses, phone numbers and maps shown on the website and used to route new leads.">
        <x-slot:actions>
            <a href="{{ route('admin.content.branches.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add branch</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        @if ($branches->isEmpty())
            <x-admin.empty icon="map-pin" title="No branches yet" text="Add your first branch so parents can find you.">
                <a href="{{ route('admin.content.branches.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add branch</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Branch</th><th>Phone</th><th>Hours</th><th>Leads</th><th>Team</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            <tr>
                                <td class="min-w-56">
                                    <div class="flex items-center gap-3">
                                        @if ($branch->image)
                                            <img src="{{ media_url($branch->image) }}" alt="" class="size-10 shrink-0 rounded-lg object-cover">
                                        @else
                                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand"><x-icon name="map-pin" class="size-5" /></span>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.content.branches.edit', $branch) }}" class="font-bold hover:text-brand">{{ $branch->name }}</a>
                                            <p class="max-w-xs truncate text-xs text-muted">{{ $branch->address_note ?: $branch->address }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">{{ $branch->phone }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $branch->working_hours ?: '—' }}</td>
                                <td>{{ $branch->leads_count }}</td>
                                <td>{{ $branch->users_count }}</td>
                                <td>
                                    @if ($branch->is_active)
                                        <span class="badge badge-green">Active</span>
                                    @else
                                        <span class="badge badge-muted">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.content.branches.edit', $branch) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <x-admin.confirm-delete :action="route('admin.content.branches.destroy', $branch)" icon
                                            :message="'Delete the '.$branch->name.' branch? This can’t be undone.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
