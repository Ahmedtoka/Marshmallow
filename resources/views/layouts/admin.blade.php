@php
    $user = auth()->user();
    $nav = [
        ['group' => null, 'items' => [
            ['Dashboard', 'admin.dashboard', 'grid', 'admin.dashboard', true],
        ]],
        ['group' => 'Sales', 'items' => [
            ['Leads', 'admin.crm.leads.index', 'users', 'admin.crm.leads.index|admin.crm.leads.show|admin.crm.leads.create|admin.crm.leads.edit', true],
            ['Pipeline board', 'admin.crm.leads.board', 'columns', 'admin.crm.leads.board', true],
            ['Follow-ups', 'admin.crm.follow-ups.index', 'calendar', 'admin.crm.follow-ups.*', true],
            ['Sales reports', 'admin.crm.reports', 'trending', 'admin.crm.reports', $user->hasRole('admin', 'sales_manager')],
        ]],
        ['group' => 'Analytics', 'items' => [
            ['Overview', 'admin.analytics.overview', 'chart', 'admin.analytics.overview', $user->hasRole('admin', 'sales_manager')],
            ['Visitors', 'admin.analytics.visitors.index', 'eye', 'admin.analytics.visitors.*', $user->hasRole('admin', 'sales_manager')],
            ['Traffic sources', 'admin.analytics.sources', 'share', 'admin.analytics.sources', $user->hasRole('admin', 'sales_manager')],
            ['Pages', 'admin.analytics.pages', 'file', 'admin.analytics.pages', $user->hasRole('admin', 'sales_manager')],
            ['Actions', 'admin.analytics.actions', 'pointer', 'admin.analytics.actions', $user->hasRole('admin', 'sales_manager')],
            ['Funnel', 'admin.analytics.funnel', 'funnel', 'admin.analytics.funnel', $user->hasRole('admin', 'sales_manager')],
        ]],
        ['group' => 'Website', 'items' => [
            ['Homepage sections', 'admin.content.sections.index', 'layers', 'admin.content.sections.*', $user->isAdmin()],
            ['Classes', 'admin.content.classrooms.index', 'cupcake', 'admin.content.classrooms.*|admin.content.classroom-activities.*', $user->isAdmin()],
            ['Activities', 'admin.content.activities.index', 'blocks', 'admin.content.activities.*', $user->isAdmin()],
            ['Camps', 'admin.content.camps.index', 'sun', 'admin.content.camps.*', $user->isAdmin()],
            ['Gallery', 'admin.content.albums.index', 'image', 'admin.content.albums.*', $user->isAdmin()],
            ['Highlights', 'admin.content.highlights.index', 'star', 'admin.content.highlights.*', $user->isAdmin()],
            ['Testimonials', 'admin.content.testimonials.index', 'chat', 'admin.content.testimonials.*', $user->isAdmin()],
            ['Partners', 'admin.content.partners.index', 'medal', 'admin.content.partners.*', $user->isAdmin()],
            ['FAQ', 'admin.content.faqs.index', 'help', 'admin.content.faqs.*', $user->isAdmin()],
            ['Branches', 'admin.content.branches.index', 'map-pin', 'admin.content.branches.*', $user->isAdmin()],
            ['Careers', 'admin.content.jobs.index', 'briefcase', 'admin.content.jobs.*|admin.content.applications.*', $user->isAdmin()],
            ['SEO', 'admin.content.seo.index', 'search', 'admin.content.seo.*', $user->isAdmin()],
        ]],
        ['group' => 'System', 'items' => [
            ['Settings', 'admin.content.settings.edit', 'gear', 'admin.content.settings.*', $user->isAdmin()],
            ['Team & access', 'admin.content.users.index', 'shield', 'admin.content.users.*', $user->isAdmin()],
        ]],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@hasSection('title')@yield('title') · @endif Marshmallow Dashboard</title>
    <link rel="icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('head')
</head>
<body class="min-h-screen" x-data="{ nav: false }">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-30 bg-ink/40 lg:hidden"></div>
    <aside :class="nav ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-ink text-white flex flex-col transition-transform lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 h-16 shrink-0">
            <img src="{{ asset('images/logo.jpg') }}" alt="" class="size-10 rounded-xl bg-white object-cover">
            <span class="leading-tight">
                <span class="block font-display text-[17px]">Marshmallow</span>
                <span class="block text-[11px] text-white/55 font-semibold">Dashboard</span>
            </span>
        </a>
        <nav class="flex-1 overflow-y-auto px-3 pb-6">
            @foreach ($nav as $section)
                @php $visible = collect($section['items'])->filter(fn ($i) => $i[4]); @endphp
                @continue($visible->isEmpty())
                @if ($section['group'])
                    <div class="nav-group">{{ $section['group'] }}</div>
                @endif
                @foreach ($visible as [$label, $route, $icon, $pattern])
                    <a href="{{ route($route) }}" @class(['nav-link', 'active' => request()->routeIs(...explode('|', $pattern))])>
                        <x-icon :name="$icon" class="size-[18px]" />
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="p-3 border-t border-white/10">
            <a href="{{ route('home') }}" target="_blank" class="nav-link">
                <x-icon name="external" class="size-[18px]" /> View website
            </a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-line flex items-center gap-3 px-4 lg:px-8">
            <button type="button" class="btn btn-ghost px-2 lg:hidden" @click="nav = true" aria-label="Open menu">
                <x-icon name="menu" />
            </button>
            <div class="flex-1 min-w-0 font-display text-lg truncate">@yield('title', 'Dashboard')</div>

            <a href="{{ route('admin.notifications.index') }}" class="relative btn btn-ghost px-2" aria-label="Notifications">
                <x-icon name="bell" />
                @if ($unreadNotifications)
                    <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 rounded-full bg-brand text-white text-[11px] font-bold grid place-items-center">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                @endif
            </a>

            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-xl pl-1 pr-2 h-10 hover:bg-canvas">
                    <span class="size-8 rounded-full bg-grape text-white grid place-items-center text-xs font-bold">{{ $user->initials() }}</span>
                    <span class="hidden sm:block text-left leading-tight">
                        <span class="block text-sm font-bold">{{ $user->name }}</span>
                        <span class="block text-[11px] text-muted">{{ $user->roleLabel() }}</span>
                    </span>
                    <x-icon name="chevron-down" class="size-4 text-muted" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 mt-2 w-48 card p-1.5 shadow-lg shadow-ink/5">
                    <a href="{{ route('admin.profile') }}" class="flex items-center gap-2 rounded-lg px-3 h-9 hover:bg-canvas font-semibold"><x-icon name="pencil" class="size-4" /> My profile</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="w-full flex items-center gap-2 rounded-lg px-3 h-9 hover:bg-canvas font-semibold text-left"><x-icon name="logout" class="size-4" /> Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8 max-w-[1400px] w-full">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-5 flex items-center gap-3 rounded-xl bg-lime/15 border border-lime/30 px-4 py-3 text-[#4A6A12] font-semibold">
                    <x-icon name="check" class="size-5" /> <span class="flex-1">{{ session('success') }}</span>
                    <button type="button" @click="show = false" aria-label="Dismiss"><x-icon name="x" class="size-4" /></button>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 flex items-center gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 font-semibold">
                    <x-icon name="alert" class="size-5" /> {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700">
                    <div class="font-bold flex items-center gap-2"><x-icon name="alert" class="size-5" /> Please fix the highlighted fields.</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
