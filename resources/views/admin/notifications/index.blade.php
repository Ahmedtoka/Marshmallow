@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')
    @php
        $icons = ['new_lead' => 'sparkle', 'lead_assigned' => 'users', 'follow_up_due' => 'clock', 'follow_up_digest' => 'calendar'];
    @endphp
    <x-admin.page-header title="Notifications" :subtitle="$unreadCount ? $unreadCount.' unread' : 'You are all caught up.'">
        <x-slot:actions>
            @if ($unreadCount)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button class="btn btn-secondary"><x-icon name="check" class="size-4" /> Mark all as read</button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <nav class="inline-flex gap-1 rounded-xl bg-white border border-line p-1 mb-4">
        @foreach (['all' => 'All', 'unread' => 'Unread'] as $k => $label)
            <a href="{{ route('admin.notifications.index', $k === 'all' ? [] : ['filter' => $k]) }}"
               @class(['rounded-lg px-3 h-9 inline-flex items-center text-sm font-bold', 'bg-ink text-white' => $filter === $k, 'text-muted hover:text-ink' => $filter !== $k])>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="card overflow-hidden">
        @if ($notifications->isEmpty())
            <x-admin.empty icon="bell" :title="$filter === 'unread' ? 'No unread notifications' : 'No notifications yet'"
                text="New leads, assignments and follow-up reminders will appear here." />
        @else
            <ul class="divide-y divide-line">
                @foreach ($notifications as $n)
                    @php $unread = ! $n->read_at; @endphp
                    <li>
                        <a href="{{ route('admin.notifications.open', $n->id) }}" @class(['flex items-start gap-3 px-5 py-4 hover:bg-canvas/60', 'bg-brand-soft/40' => $unread])>
                            <span @class(['size-9 rounded-xl grid place-items-center shrink-0', 'bg-brand text-white' => $unread, 'bg-canvas text-muted' => ! $unread])>
                                <x-icon :name="$icons[$n->data['kind'] ?? ''] ?? 'bell'" class="size-4" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span @class(['block', 'font-bold' => $unread, 'font-semibold' => ! $unread])>{{ $n->data['title'] ?? 'Notification' }}</span>
                                @if (! empty($n->data['body']))<span class="block text-sm text-muted truncate">{{ $n->data['body'] }}</span>@endif
                            </span>
                            <span class="flex items-center gap-2 text-xs text-muted whitespace-nowrap">
                                <x-admin.crm.when :date="$n->created_at" short />
                                @if ($unread)<span class="size-2 rounded-full bg-brand" aria-label="Unread"></span>@endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
