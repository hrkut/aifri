<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">
<div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    @include('layouts.admin-navigation')
    <main class="px-4 py-6 sm:px-6 lg:px-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</div>
</body>
</html>

