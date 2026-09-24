<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · Marshmallow Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-192.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen grid place-items-center px-4 py-10">
    <main class="w-full max-w-sm">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Marshmallow Child Development Center" class="mx-auto w-44 rounded-3xl">
        </div>
        <div class="card p-6 sm:p-7">
            <h1 class="font-display text-2xl">Sign in to the dashboard</h1>
            <p class="text-muted mt-1 mb-6">Use the email your admin set up for you.</p>

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" @class(['input', 'input-error' => $errors->has('email')])>
                    @error('email') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-muted">
                    <input type="checkbox" name="remember" value="1" class="checkbox"> Keep me signed in
                </label>
                <button class="btn btn-primary w-full">Sign in</button>
            </form>
        </div>
    </main>
</body>
</html>
