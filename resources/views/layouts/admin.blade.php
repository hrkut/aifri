<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Admin</title>
    <style>[x-cloak]{display:none !important;}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">
<div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    @include('layouts.admin-navigation')
    <main class="px-4 py-6 sm:px-6 lg:px-8">
        @php($workingConference = \App\Models\Conference::current())
        @if ($workingConference->isReadOnly())
            <div class="max-w-5xl mx-auto mb-4 p-3 rounded-lg bg-sky-950/50 border border-sky-800 text-sky-100 text-sm">
                🔒 Prezeráte archívny ročník <strong>{{ $workingConference->year }}</strong> – len na čítanie.
                <a href="{{ route('admin.conferences.index') }}" class="underline hover:text-white">Zmeniť ročník</a>
            </div>
        @elseif (! $workingConference->is_active)
            <div class="max-w-5xl mx-auto mb-4 p-3 rounded-lg bg-amber-950/50 border border-amber-800 text-amber-100 text-sm">
                Pracujete s ročníkom <strong>{{ $workingConference->year }}</strong>, ktorý nie je zverejnený na webe.
                <a href="{{ route('admin.conferences.index') }}" class="underline hover:text-white">Zmeniť ročník</a>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-5xl mx-auto mb-4 p-3 rounded-lg bg-red-950/50 border border-red-800 text-red-100 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="max-w-5xl mx-auto mb-4 p-3 rounded-lg bg-emerald-950/50 border border-emerald-800 text-emerald-100 text-sm">
                {{ session('success') }}
            </div>
        @endif
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</div>
</body>
</html>

