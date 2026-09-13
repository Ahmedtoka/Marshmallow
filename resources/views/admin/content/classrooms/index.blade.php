@extends('layouts.admin')
@section('title', 'Classes')

@section('content')
    <x-admin.page-header title="Classes" subtitle="Your age groups. Each class has its own page, activities and photos on the website.">
        <x-slot:actions>
            <a href="{{ route('classes.index') }}" target="_blank" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View on website</a>
            <a href="{{ route('admin.content.classrooms.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add class</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Age map --}}
    <div class="card card-pad mb-6">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="card-title">Age map</h2>
                <p class="hint">How active classes cover ages from birth to 6 years. The class finder on the website uses these ranges to place each child.</p>
            </div>
            @if (empty($map['segments']))
                <span class="badge badge-muted">No active classes</span>
            @elseif (empty($map['issues']))
                <span class="badge badge-green"><x-icon name="check" class="size-3.5" /> No gaps or overlaps</span>
            @else
                <span class="badge badge-red"><x-icon name="alert" class="size-3.5" /> {{ count($map['issues']) }} {{ count($map['issues']) === 1 ? 'problem' : 'problems' }} to check</span>
            @endif
        </div>

        <div class="-mx-5 overflow-x-auto px-5">
            <div class="min-w-[620px] px-3">
                <div class="relative h-14 rounded-xl bg-canvas">
                    @foreach ($map['segments'] as $segment)
                        <div class="absolute inset-y-2 flex items-center justify-center overflow-hidden rounded-lg px-1 text-[11px] font-bold text-white ring-2 ring-white"
                             style="left: {{ $segment['left'] }}%; width: {{ $segment['width'] }}%; background: {{ $segment['color'] }}"
                             title="{{ $segment['name'] }} · {{ $segment['label'] }}">
                            <span class="truncate">{{ $segment['name'] }}</span>
                        </div>
                    @endforeach
                    @foreach ($map['issues'] as $issue)
                        <div @class([
                                'pointer-events-none absolute -inset-y-1 rounded-lg border-2 border-dashed',
                                'border-red-400 bg-red-100/60' => $issue['type'] === 'gap',
                                'border-red-600 bg-red-500/25' => $issue['type'] === 'overlap',
                             ])
                             style="left: {{ $issue['left'] }}%; width: {{ max($issue['width'], 0.8) }}%"></div>
                    @endforeach
                </div>
                <div class="relative mt-1.5 h-5 text-[11px] font-semibold text-muted">
                    @for ($m = 0; $m <= $scale; $m += 6)
                        <span class="absolute -translate-x-1/2 whitespace-nowrap" style="left: {{ round($m / $scale * 100, 3) }}%">
                            {{ $m === 0 ? '0' : ($m % 12 === 0 ? ($m / 12).'y' : $m.'m') }}
                        </span>
                    @endfor
                </div>
            </div>
        </div>

        @if (! empty($map['issues']) || ($map['first'] ?? 0) > 0 || (! empty($map['segments']) && ! $map['openEnded']))
            <ul class="mt-3 space-y-1.5 text-sm">
                @foreach ($map['issues'] as $issue)
                    <li class="flex gap-2 font-semibold text-red-700"><x-icon name="alert" class="mt-0.5 size-4" /> {{ $issue['text'] }}</li>
                @endforeach
                @if (($map['first'] ?? 0) > 0)
                    <li class="flex gap-2 text-muted"><x-icon name="clock" class="mt-0.5 size-4" /> Your youngest class starts at {{ $map['first'] }} months; younger children won’t be matched to a class.</li>
                @endif
                @if (! empty($map['segments']) && ! $map['openEnded'])
                    <li class="flex gap-2 text-muted"><x-icon name="clock" class="mt-0.5 size-4" /> Your oldest class ends at {{ $map['last'] }} months; older children won’t be matched to a class.</li>
                @endif
            </ul>
        @endif
    </div>

    {{-- List --}}
    <div class="card overflow-hidden">
        @if ($classrooms->isEmpty())
            <x-admin.empty icon="cupcake" title="No classes yet" text="Add your first class with its age range, activities and photos.">
                <a href="{{ route('admin.content.classrooms.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Add class</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Class</th><th>Ages</th><th>Icon</th><th>Activities</th><th>Photos</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($classrooms as $classroom)
                            <tr>
                                <td class="min-w-56">
                                    <div class="flex items-center gap-3">
                                        <span class="size-3.5 shrink-0 rounded-full ring-4 ring-canvas" style="background: {{ $classroom->color }}"></span>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.content.classrooms.edit', $classroom) }}" class="font-bold hover:text-brand">{{ $classroom->name }}</a>
                                            @if ($classroom->tagline)
                                                <p class="max-w-xs truncate text-xs text-muted">{{ $classroom->tagline }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-semibold">{{ $classroom->ageRangeLabel() }}</div>
                                    <div class="text-xs text-muted">{{ $classroom->min_months }}–{{ $classroom->max_months ?? '∞' }} months</div>
                                </td>
                                <td class="text-muted">{{ $classroom->icon }}</td>
                                <td>{{ $classroom->classroom_activities_count }}</td>
                                <td>{{ $classroom->photos_count }}</td>
                                <td>
                                    @if ($classroom->is_active)
                                        <span class="badge badge-green">Active</span>
                                    @else
                                        <span class="badge badge-muted">Hidden</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.content.classrooms.edit', $classroom) }}" class="btn btn-secondary btn-sm">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
