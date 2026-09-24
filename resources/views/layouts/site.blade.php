@php
    $seo = \App\Models\SeoPage::for($seoKey ?? 'home');
    $siteName = setting('site_name', 'Marshmallow Child Development Center');
    $pageTitle = trim($__env->yieldContent('title')) ?: ($seo?->title ?: $siteName);
    $pageDescription = trim($__env->yieldContent('description')) ?: ($seo?->description ?: setting('footer_about'));
    $logoUrl = media_url(setting('logo_path')) ?: asset('images/logo.jpg');
    $headerLogoUrl = media_url(setting('logo_path')) ?: asset('images/logo-header.png');
    $pageImage = trim($__env->yieldContent('og_image')) ?: (media_url($seo?->og_image) ?: (media_url(setting('default_og_image')) ?: $logoUrl));
    if (! \Illuminate\Support\Str::startsWith($pageImage, ['http://', 'https://'])) {
        $pageImage = url($pageImage);
    }
    $canonical = url()->current();

    $siteBranches = \App\Models\Branch::active()->get();

    // Three places to go and one thing to do — everything else lives inside these pages.
    $nav = [
        ['Classes', route('classes.index'), 'classes.*'],
        ['Gallery', route('gallery.index'), 'gallery.*'],
        ['Visit us', route('visit'), 'visit'],
    ];

    // Secondary pages, kept in the footer so the menu stays short.
    $footerNav = [
        ['Activities', route('activities.index')],
        ['Camps', route('camps.index')],
        ['Safety & care', route('safety')],
        ['About us', route('about')],
        ['Branches', route('branches')],
        ['Careers', route('careers')],
    ];

    $socials = collect([
        'facebook' => ['Facebook', setting('facebook_url')],
        'instagram' => ['Instagram', setting('instagram_url')],
        'tiktok' => ['TikTok', setting('tiktok_url')],
        'youtube' => ['YouTube', setting('youtube_url')],
        'messenger' => ['Messenger', setting('messenger_url')],
    ])->filter(fn ($s) => filled($s[1]));

    $openingHours = 'Su-Th 08:00-16:00';
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => ['ChildCare', 'Preschool'],
        'name' => $siteName,
        'slogan' => setting('tagline'),
        'url' => url('/'),
        'logo' => $logoUrl,
        'image' => $pageImage,
        'email' => setting('email'),
        'telephone' => setting('main_phone') ? '+2'.preg_replace('/\D/', '', setting('main_phone')) : null,
        'openingHours' => $openingHours,
        'sameAs' => $socials->pluck(1)->values()->all(),
        'department' => $siteBranches->map(fn ($b) => [
            '@type' => ['ChildCare', 'Preschool'],
            'name' => $siteName.' – '.$b->name,
            'url' => route('branches').'#'.$b->slug,
            'telephone' => '+2'.preg_replace('/\D/', '', $b->phone),
            'openingHours' => $openingHours,
            'hasMap' => $b->map_url,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $b->address,
                'addressLocality' => $b->name,
                'addressRegion' => 'Giza',
                'addressCountry' => 'EG',
            ],
            'sameAs' => $socials->pluck(1)->values()->all(),
        ])->values()->all(),
    ];
    $jsonLd = array_filter($jsonLd, fn ($v) => $v !== null && $v !== []);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($pageDescription), 300) }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mm-track" content="{{ route('track.collect') }}">
    <meta name="theme-color" content="#E8177F">
    @stack('meta')

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($pageDescription), 300) }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($pageDescription), 200) }}">
    <meta name="twitter:image" content="{{ $pageImage }}">

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:ital,wght@0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($ga4 = setting('ga4_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', @js($ga4));
        </script>
    @endif

    @if ($pixel = setting('meta_pixel_id'))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @js($pixel));
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ urlencode($pixel) }}&ev=PageView&noscript=1"></noscript>
    @endif

    {!! setting('head_scripts') !!}

    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @stack('head')
</head>
<body class="flex min-h-dvh flex-col bg-white text-ink">
    <a href="#main" class="sr-only z-[60] rounded-full bg-ink px-4 py-2 font-bold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    @if (setting('announcement_visible') === '1' && setting('announcement'))
        <div class="bg-sun px-5 py-2 text-center text-sm font-bold text-ink sm:text-[0.95rem]">
            <a href="{{ route('enroll') }}" class="inline-flex items-center gap-2 hover:underline" data-track="cta_click" data-track-label="Announcement bar">
                <x-icon name="sparkle" class="size-4 text-pink-600" />
                <span>{{ setting('announcement') }}</span>
            </a>
        </div>
    @endif

    <div x-data="{ menu: false }" @keydown.escape.window="menu = false" class="sticky top-0 z-40">
        <header class="border-b-2 border-line-soft bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85">
            <div class="mx-auto flex h-[4.25rem] max-w-6xl items-center gap-3 px-4 sm:h-20 sm:px-8">
                <a href="{{ route('home') }}" class="shrink-0" aria-label="{{ $siteName }} home">
                    <img src="{{ $headerLogoUrl }}" alt="{{ $siteName }}" width="520" height="292" class="h-12 w-auto sm:h-[3.6rem]">
                </a>

                <nav class="ml-auto hidden lg:block" aria-label="Main">
                    <ul class="flex items-center gap-0.5">
                        @foreach ($nav as [$label, $url, $pattern])
                            @php $active = request()->routeIs($pattern); @endphp
                            <li>
                                <a href="{{ $url }}" @class([
                                    'rounded-full px-3.5 py-2 font-display text-[1.02rem] font-medium transition-colors',
                                    'bg-blush text-pink-600' => $active,
                                    'text-ink hover:text-pink-600' => ! $active,
                                ]) @if ($active) aria-current="page" @endif>{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <a href="{{ route('enroll') }}" class="btn btn-primary btn-sm ml-auto lg:ml-3 sm:min-h-11 sm:px-5" data-track="cta_click" data-track-label="Header – Book a visit">Book a visit</a>

                <button type="button" class="grid size-11 place-items-center rounded-full border-2 border-line text-ink lg:hidden" @click="menu = !menu" :aria-expanded="menu.toString()" aria-controls="mobile-menu">
                    <span class="sr-only">Menu</span>
                    <x-icon name="menu" class="size-5" x-show="!menu" />
                    <x-icon name="x" class="size-5" x-show="menu" x-cloak />
                </button>
            </div>
        </header>

        <div id="mobile-menu" x-show="menu" x-cloak x-transition.opacity @click.outside="menu = false"
            class="absolute inset-x-0 top-full max-h-[calc(100dvh-5rem)] overflow-y-auto border-b-2 border-line-soft bg-white px-4 pb-6 pt-2 lg:hidden">
            <nav aria-label="Mobile">
                <ul class="grid grid-cols-2 gap-2">
                    @foreach ($nav as [$label, $url, $pattern])
                        <li>
                            <a href="{{ $url }}" @class([
                                'block rounded-2xl px-4 py-3 font-display text-lg font-medium',
                                'bg-pink-100 text-pink-600' => request()->routeIs($pattern),
                                'bg-blush' => ! request()->routeIs($pattern),
                            ])>{{ $label }}</a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('careers') }}" class="block rounded-2xl bg-blush px-4 py-3 font-display text-lg font-medium">Careers</a></li>
                </ul>
            </nav>
            <div class="mt-4 grid gap-2">
                @foreach ($siteBranches as $branch)
                    <a href="{{ $branch->telLink() }}" class="flex items-center justify-between rounded-2xl border-2 border-line px-4 py-3" data-track-label="Call {{ $branch->name }}">
                        <span>
                            <span class="block text-sm font-bold text-ink-soft">Call {{ $branch->name }}</span>
                            <span class="font-display text-lg font-semibold">{{ $branch->phone }}</span>
                        </span>
                        <x-icon name="phone" class="size-5 text-pink" />
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <main id="main" class="flex-1">
        @if (session('status'))
            <div class="mx-auto mt-4 max-w-6xl px-4 sm:px-8" role="status">
                <p class="flex items-center gap-2 rounded-2xl border-2 border-lime bg-lime-50 px-4 py-3 font-bold"><x-icon name="check" class="size-5 text-lime-700" /> {{ session('status') }}</p>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="mt-auto bg-ink text-white/85">
        <div class="mx-auto max-w-6xl px-5 pb-28 pt-14 sm:px-8 sm:pb-12">
            <div class="grid gap-10 lg:grid-cols-[1.3fr_1fr_1.6fr]">
                <div>
                    <p class="font-display text-2xl font-semibold text-white">{{ setting('short_name', 'Marshmallow') }}</p>
                    <p class="mt-1 font-display text-sun">{{ setting('tagline', 'A Unique Way of Learning') }}</p>
                    @if (setting('footer_about'))
                        <p class="mt-4 max-w-sm leading-relaxed">{{ setting('footer_about') }}</p>
                    @endif
                    @if ($socials->isNotEmpty())
                        <ul class="mt-5 flex flex-wrap gap-2">
                            @foreach ($socials as $key => [$label, $url])
                                <li>
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border-2 border-white/20 px-3.5 py-1.5 text-sm font-bold text-white hover:border-pink hover:bg-white/5">
                                        @if (in_array($key, ['facebook', 'instagram']))
                                            <x-icon :name="$key" class="size-4" />
                                        @elseif ($key === 'messenger')
                                            <x-icon name="chat" class="size-4" />
                                        @endif
                                        {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-8 lg:grid-cols-1">
                    <nav aria-label="Footer">
                        <p class="font-display text-lg font-semibold text-white">Explore</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($nav as [$label, $url, $pattern])
                                <li><a href="{{ $url }}" class="hover:text-white hover:underline">{{ $label }}</a></li>
                            @endforeach
                            @foreach ($footerNav as [$label, $url])
                                <li><a href="{{ $url }}" class="hover:text-white hover:underline">{{ $label }}</a></li>
                            @endforeach
                            <li><a href="{{ route('enroll') }}" class="hover:text-white hover:underline">Book a visit</a></li>
                        </ul>
                    </nav>
                    <div>
                        <p class="font-display text-lg font-semibold text-white">Opening hours</p>
                        <dl class="mt-3 space-y-2.5 text-[0.95rem]">
                            @if (setting('working_days'))
                                <div><dt class="font-bold text-white">{{ setting('working_days') }}</dt><dd>{{ setting('working_hours') }}</dd></div>
                            @endif
                            @if (setting('weekend'))
                                <div><dt class="font-bold text-white">{{ setting('weekend') }}</dt><dd>Closed</dd></div>
                            @endif
                            @if (setting('after_school'))
                                <div><dd>{{ setting('after_school') }}</dd></div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    @foreach ($siteBranches as $branch)
                        <div class="rounded-[1.4rem] border-2 border-white/15 p-5">
                            <p class="font-display text-lg font-semibold text-white">{{ $branch->name }}</p>
                            <p class="mt-1.5 text-[0.95rem] leading-relaxed">{{ $branch->address }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ $branch->telLink() }}" class="inline-flex items-center gap-1.5 rounded-full bg-white px-3.5 py-2 text-sm font-bold text-ink hover:bg-sun-100" data-track-label="Call {{ $branch->name }}">
                                    <x-icon name="phone" class="size-4 text-pink" /> {{ $branch->phone }}
                                </a>
                                <a href="{{ $branch->whatsappLink('Hello Marshmallow '.$branch->name.', I would like to ask about your nursery.') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full border-2 border-white/25 px-3.5 py-1.5 text-sm font-bold text-white hover:border-white" data-track-label="WhatsApp {{ $branch->name }}">
                                    <x-icon name="whatsapp" class="size-4" /> WhatsApp
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-3 border-t-2 border-white/10 pt-6 text-sm sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ $siteName }}</p>
                @if (setting('hashtags'))
                    <p class="break-words text-white/60">{{ setting('hashtags') }}</p>
                @endif
            </div>
        </div>
    </footer>

    {{-- Floating WhatsApp with a branch chooser --}}
    @if ($siteBranches->isNotEmpty())
        <div x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false" class="fixed bottom-4 right-4 z-50 flex flex-col items-end gap-3 sm:bottom-6 sm:right-6">
            <div x-show="open" x-cloak x-transition.origin.bottom.right id="wa-chooser" class="w-[17.5rem] rounded-[1.5rem] border-2 border-line bg-white p-3 text-ink" role="dialog" aria-label="Chat on WhatsApp">
                <p class="px-2 pb-2 pt-1 font-display text-lg font-semibold">Chat with a branch</p>
                <ul class="space-y-1.5">
                    @foreach ($siteBranches as $branch)
                        <li>
                            <a href="{{ $branch->whatsappLink('Hello Marshmallow '.$branch->name.', I would like to ask about your nursery.') }}" target="_blank" rel="noopener"
                                class="flex items-center gap-3 rounded-2xl bg-blush px-3 py-2.5 hover:bg-pink-100" data-track-label="WhatsApp {{ $branch->name }} – floating button">
                                <span class="grid size-9 place-items-center rounded-full bg-[#25D366] text-white"><x-icon name="whatsapp" class="size-5" /></span>
                                <span class="min-w-0">
                                    <span class="block font-bold leading-tight">{{ $branch->name }}</span>
                                    <span class="block text-sm text-ink-soft">{{ $branch->whatsapp ?: $branch->phone }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="wa-chooser"
                class="grid size-14 place-items-center rounded-full bg-[#25D366] text-white ring-4 ring-white transition-transform hover:scale-105"
                data-track="cta_click" data-track-label="Floating WhatsApp – open chooser">
                <span class="sr-only">Chat with us on WhatsApp</span>
                <svg viewBox="0 0 32 32" class="size-8" fill="currentColor" aria-hidden="true" x-show="!open"><path d="M16.02 3C8.84 3 3 8.83 3 16c0 2.3.6 4.53 1.74 6.5L3 29l6.68-1.75A12.96 12.96 0 0 0 16.02 29C23.2 29 29 23.17 29 16S23.2 3 16.02 3Zm0 23.63c-2.03 0-4.01-.55-5.74-1.58l-.41-.24-3.96 1.04 1.06-3.86-.27-.4A10.6 10.6 0 0 1 5.4 16c0-5.86 4.77-10.63 10.63-10.63S26.63 10.14 26.63 16 21.87 26.63 16.02 26.63Zm5.83-7.96c-.32-.16-1.89-.93-2.18-1.04-.29-.1-.5-.16-.72.16-.21.32-.82 1.04-1 1.25-.19.21-.37.24-.69.08-.32-.16-1.35-.5-2.57-1.59-.95-.85-1.59-1.9-1.78-2.22-.19-.32-.02-.49.14-.65.14-.14.32-.37.48-.56.16-.19.21-.32.32-.53.1-.21.05-.4-.03-.56-.08-.16-.72-1.73-.98-2.37-.26-.62-.52-.54-.72-.55h-.61c-.21 0-.56.08-.85.4-.29.32-1.12 1.09-1.12 2.66s1.14 3.08 1.3 3.3c.16.21 2.25 3.43 5.44 4.81.76.33 1.35.52 1.81.67.76.24 1.46.21 2.01.13.61-.09 1.89-.77 2.16-1.52.27-.75.27-1.39.19-1.52-.08-.13-.29-.21-.61-.37Z"/></svg>
                <x-icon name="x" class="size-6" x-show="open" x-cloak />
            </button>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
