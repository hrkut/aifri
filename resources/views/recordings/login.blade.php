@extends('layouts.public')

@section('title', __('Záznamy z minulých ročníkov'))
@section('meta_description', __('Záznamy z konferencie o umelej inteligencii vo vzdelávaní'))

@section('content')

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

<x-site.hero :conference="$conference"
             :title="__('ZÁZNAMY Z MINULÝCH ROČNÍKOV')"
             :sub="__('Videozáznamy a prezentácie príspevkov')" />

<div class="stack">
    <div class="card">
        <h2>{{ __('Prístup k záznamom') }}</h2>
        <p style="margin-bottom: 1.5rem;">
            {{ __('Zadajte heslo na prístup k záznamom a prezentáciám z minulých ročníkov konferencie:') }}
        </p>

        <form method="POST" action="{{ lroute('recordings.authenticate') }}" novalidate style="max-width: 420px;">
            @csrf

            <div class="login-form-group">
                <label for="password">{{ __('Heslo *') }}</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="off"
                    required
                    autofocus
                    placeholder="{{ __('Zadajte heslo') }}"
                    class="@error('password') is-invalid @enderror"
                >
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit">{{ __('Pristúpiť k záznamom') }}</button>
        </form>

    </div>
</div>
@endsection

