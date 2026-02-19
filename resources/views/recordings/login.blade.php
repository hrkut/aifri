@extends('layouts.public')

@section('title', 'Príspevky z konferencie')
@section('meta_description', 'Záznamy z konferencie o umelej inteligencii vo vzdelávaní')

@section('content')
<a href="{{ route('home') }}" class="siteHeaderLink">
    <div class="siteHeader">
        <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
    </div>
</a>

<style>
    .login-form-group {
        margin-bottom: 1.5rem;
    }

    .login-form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #ffb81c;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .login-form-group input[type="password"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid rgba(159, 231, 255, 0.3);
        border-radius: 0.4rem;
        font-size: 1rem;
        box-sizing: border-box;
        background: rgba(11, 75, 103, 0.2);
        color: #f3f7fb;
        transition: all 0.2s ease;
    }

    .login-form-group input[type="password"]:focus {
        outline: none;
        border-color: rgba(159, 231, 255, 0.6);
        background: rgba(11, 75, 103, 0.35);
        box-shadow: 0 0 0 3px rgba(159, 231, 255, 0.1);
    }

    .login-form-group input[type="password"]::placeholder {
        color: rgba(203, 213, 225, 0.5);
    }

    .error-message {
        color: #ff6b6b;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    .btn-submit {
        width: 100%;
        padding: 0.75rem;
        background: rgba(255, 184, 28, 0.16);
        color: #ffb81c;
        border: 1px solid rgba(255, 184, 28, 0.35);
        border-radius: 0.4rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit:hover {
        background: rgba(255, 184, 28, 0.3);
        border-color: rgba(255, 184, 28, 0.6);
    }

    .btn-submit:active {
        transform: scale(0.98);
    }
</style>

<div class="stack" style="max-width: 90vw; margin: 2rem auto; padding: 0 1rem;">
    <div class="card" style="background: rgba(11, 75, 103, 0.35); padding: 2rem; max-width: 500px; margin: 2rem auto;">
        <h1 style="color: #9fe7ff; margin-top: 0; margin-bottom: 0.5rem; text-align: center;">🔐 Príspevky z konferencie</h1>
        <p style="color: #cbd5e1; text-align: center; margin-bottom: 1.5rem; font-size: 0.95rem;">
            Zadajte heslo na prístup k záznamom z konferencie:
        </p>

        <form method="POST" action="{{ route('recordings.authenticate') }}" novalidate>
            @csrf

            <div class="login-form-group">
                <label for="password">Heslo *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="off"
                    required
                    autofocus
                    placeholder="Zadajte heslo"
                    class="@error('password') is-invalid @enderror"
                >
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">Prístupiť k záznamom</button>
        </form>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="{{ route('home') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background: rgba(159, 231, 255, 0.2); color: #00e5cc; text-decoration: none; border-radius: 0.5rem; font-weight: 500; border: 1px solid rgba(159, 231, 255, 0.3); transition: all 0.2s ease;">
                ← Späť na hlavnú stránku
            </a>
        </div>
    </div>
</div>
@endsection

