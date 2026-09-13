@extends('layouts.admin')
@section('title', 'Partners')

@section('content')
    <x-admin.page-header title="Partners" subtitle="Partner schools, certifications and recognitions shown on the homepage and About page.">
        <x-slot:actions>
            <a href="{{ route('admin.content.partners.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add partner</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="card overflow-hidden">
        @if ($partners->isEmpty())
            <x-admin.empty icon="medal" title="No partners yet" text="Add the schools your graduates move on to, or certificates your team holds.">
                <a href="{{ route('admin.content.partners.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add partner</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Partner</th><th>Type</th><th>Website</th><th>Order</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($partners as $partner)
                            <tr>
                                <td class="min-w-64">
                                    <div class="flex items-center gap-3">
                                        @if ($partner->logo)
                                            <img src="{{ media_url($partner->logo) }}" alt="" class="size-10 shrink-0 rounded-lg border border-line bg-white object-contain p-1">
                                        @else
                                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-canvas text-muted"><x-icon name="medal" /></span>
                                        @endif
                                        <a href="{{ route('admin.content.partners.edit', $partner) }}" class="font-bold hover:text-brand">{{ $partner->name }}</a>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-muted">{{ \App\Models\Partner::TYPES[$partner->type] ?? $partner->type }}</td>
                                <td class="max-w-48 truncate">
                                    @if ($partner->website)
                                        <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="text-grape hover:underline">{{ parse_url($partner->website, PHP_URL_HOST) ?: $partner->website }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $partner->sort_order }}</td>
                                <td>
                                    @if ($partner->is_visible)
                                        <span class="badge badge-green">Visible</span>
                                    @else
                                        <span class="badge badge-muted">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.content.partners.edit', $partner) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <x-admin.confirm-delete :action="route('admin.content.partners.destroy', $partner)" icon :message="'Delete '.$partner->name.'?'" />
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
