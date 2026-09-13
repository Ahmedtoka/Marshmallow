@extends('layouts.admin')
@section('title', 'Camps')

@section('content')
    <x-admin.page-header title="Camps" subtitle="Holiday camps shown on the Camps page. Featured camps also appear on the homepage first.">
        <x-slot:actions>
            <a href="{{ route('camps.index') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
            <a href="{{ route('admin.content.camps.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add camp</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        @if ($camps->isEmpty())
            <x-admin.empty icon="sun" title="No camps yet" text="Add your next summer or winter camp.">
                <a href="{{ route('admin.content.camps.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add camp</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Camp</th><th>Season</th><th>Dates</th><th>Ages</th><th>Photos</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($camps as $camp)
                            <tr>
                                <td class="min-w-60">
                                    <div class="flex items-center gap-3">
                                        @if ($camp->cover_image)
                                            <img src="{{ media_url($camp->cover_image) }}" alt="" class="size-10 shrink-0 rounded-lg object-cover">
                                        @else
                                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-honey/15 text-honey"><x-icon name="sun" /></span>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.content.camps.edit', $camp) }}" class="font-bold hover:text-brand">{{ $camp->title }}</a>
                                            <p class="max-w-xs truncate text-xs text-muted">{{ $camp->summary }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">{{ $camp->seasonLabel() }} {{ $camp->year }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $camp->datesLabel() ?? 'Not set' }}</td>
                                <td class="whitespace-nowrap">{{ $camp->age_from }}–{{ $camp->age_to }} years</td>
                                <td>{{ $camp->photos_count }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @if ($camp->is_active)
                                            <span class="badge badge-green">Active</span>
                                        @else
                                            <span class="badge badge-muted">Hidden</span>
                                        @endif
                                        @if ($camp->is_featured)
                                            <span class="badge badge-pink"><x-icon name="star" class="size-3" /> Featured</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.content.camps.edit', $camp) }}" class="btn btn-secondary btn-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
