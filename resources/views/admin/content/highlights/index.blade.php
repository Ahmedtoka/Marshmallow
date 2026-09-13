@extends('layouts.admin')
@section('title', 'Highlights')

@section('content')
    <x-admin.page-header title="Highlights" subtitle="Short icon cards that explain what makes Marshmallow special.">
        <x-slot:actions>
            <a href="{{ route('admin.content.highlights.create', ['group' => $group]) }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add highlight</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0">
        @foreach (\App\Models\Highlight::GROUPS as $key => $label)
            <a href="{{ route('admin.content.highlights.index', ['group' => $key]) }}" @class(['btn btn-sm shrink-0', 'btn-primary' => $group === $key, 'btn-secondary' => $group !== $key])>
                {{ $label }} <span class="opacity-70">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="mb-4 flex items-center gap-2 rounded-xl border border-teal/30 bg-teal/10 px-4 py-2.5 text-sm font-semibold text-[#146B73]">
        <x-icon name="eye" class="size-4" /> Where it appears: {{ $where[$group] ?? '' }}
    </div>

    <div class="card overflow-hidden">
        @if ($highlights->isEmpty())
            <x-admin.empty icon="star" title="No highlights in this group" text="Add a few short cards with an icon, a title and one sentence.">
                <a href="{{ route('admin.content.highlights.create', ['group' => $group]) }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add highlight</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th class="w-16">Order</th><th>Highlight</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($highlights as $highlight)
                            <tr>
                                <td class="text-muted">{{ $highlight->sort_order }}</td>
                                <td class="min-w-72">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl" style="background: {{ ($highlight->color ?: '#8479BD') }}1F; color: {{ $highlight->color ?: '#8479BD' }}">
                                            <x-icon :name="$highlight->icon" />
                                        </span>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.content.highlights.edit', $highlight) }}" class="font-bold hover:text-brand">{{ $highlight->title }}</a>
                                            <p class="max-w-xl truncate text-xs text-muted">{{ $highlight->description }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($highlight->is_visible)
                                        <span class="badge badge-green">Visible</span>
                                    @else
                                        <span class="badge badge-muted">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.content.highlights.edit', $highlight) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <x-admin.confirm-delete :action="route('admin.content.highlights.destroy', $highlight)" icon :message="'Delete “'.$highlight->title.'”?'" />
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
