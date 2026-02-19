@extends('layouts.public')

@section('title', 'Session Test')

@section('content')
<a href="{{ route('home') }}" class="siteHeaderLink">
    <div class="siteHeader">
        <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
    </div>
</a>

<div class="stack" style="max-width: 90vw; margin: 2rem auto; padding: 0 1rem;">
    <div class="card" style="background: rgba(11, 75, 103, 0.35); padding: 2rem;">
        <h1 style="color: #9fe7ff;">🔍 Session Test</h1>

        <div style="background: rgba(0, 0, 0, 0.3); padding: 1rem; border-radius: 4px; margin: 1rem 0;">
            <h3 style="color: #ffb81c;">Session Data:</h3>
            <pre style="color: #cbd5e1; overflow: auto;">
{{ json_encode(session()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </pre>
        </div>

        <div style="background: rgba(0, 0, 0, 0.3); padding: 1rem; border-radius: 4px; margin: 1rem 0;">
            <h3 style="color: #ffb81c;">recordings_authenticated:</h3>
            <p style="color: #cbd5e1; font-size: 1.2rem;">
                @if(session('recordings_authenticated') === true)
                    ✅ TRUE (prihlásený si)
                @else
                    ❌ FALSE alebo NULL (nie si prihlásený)
                @endif
            </p>
        </div>

        <p style="color: #cbd5e1;">
            <a href="{{ route('recordings.login') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background: rgba(255, 184, 28, 0.16); color: #ffb81c; text-decoration: none; border-radius: 0.5rem; font-weight: 500; border: 1px solid rgba(255, 184, 28, 0.35);">
                Spusť login
            </a>
        </p>
    </div>
</div>
@endsection

