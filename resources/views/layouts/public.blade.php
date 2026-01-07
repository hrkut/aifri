<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'Konferencia AI'))</title>
        @hasSection('meta_description')
            <meta name="description" content="@yield('meta_description')">
        @endif
        <link rel="stylesheet" href="{{ asset('css/conference.css') }}">
    </head>
    <body>
        <div class="wrap">
            @yield('content')
        </div>
    </body>
</html>

