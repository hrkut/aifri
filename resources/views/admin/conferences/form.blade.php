@extends('layouts.admin')

@section('content')
@php
    /** @var \App\Models\Conference $conference */
    $isNew = ! $conference->exists;
    $input = 'w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2';
    $dateValue = fn (string $field) => old($field, $conference->{$field}?->format('Y-m-d'));
@endphp

<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">{{ $isNew ? 'Nový ročník' : 'Ročník ' . $conference->year }}</h1>
        <a href="{{ route('admin.conferences.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
            Späť
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-950/40 border border-red-900 rounded-lg text-red-200">
            <p class="font-semibold mb-2">Formulár obsahuje chyby:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $isNew ? route('admin.conferences.store') : route('admin.conferences.update', $conference) }}"
          class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <div class="p-6 space-y-6">
            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Termíny</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="year">Ročník (rok) *</label>
                        <input id="year" name="year" type="number" min="2000" max="2100" class="{{ $input }}" value="{{ old('year', $conference->year) }}" required>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="date">Dátum konania *</label>
                        <input id="date" name="date" type="date" class="{{ $input }}" value="{{ $dateValue('date') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="registration_deadline">Termín registrácie</label>
                        <input id="registration_deadline" name="registration_deadline" type="date" class="{{ $input }}" value="{{ $dateValue('registration_deadline') }}">
                        <p class="mt-1 text-xs text-slate-500">Platí pre pasívnu účasť. Bez termínu je registrácia otvorená až do dňa konferencie.</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="contribution_deadline">Termín na príspevky (aktívna účasť)</label>
                        <input id="contribution_deadline" name="contribution_deadline" type="date" class="{{ $input }}" value="{{ $dateValue('contribution_deadline') }}">
                        <p class="mt-1 text-xs text-slate-500">Po tomto dni sa dá zaregistrovať už len na pasívnu účasť. Bez termínu platí termín registrácie.</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="notification_date">Notifikácia autorov</label>
                        <input id="notification_date" name="notification_date" type="date" class="{{ $input }}" value="{{ $dateValue('notification_date') }}">
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Online pripojenie</h2>
                <label class="block text-sm text-slate-300 mb-1" for="teams_url">Odkaz na stretnutie v MS Teams</label>
                <input id="teams_url" name="teams_url" type="url" class="{{ $input }}" value="{{ old('teams_url', $conference->teams_url) }}" placeholder="https://teams.microsoft.com/l/meetup-join/...">
                <p class="mt-1 text-xs text-slate-500">Kým nie je vyplnený, tlačidlá na pripojenie sa na webe nezobrazujú.</p>
            </section>

            @if ($isNew)
                <section>
                    <h2 class="text-lg font-semibold text-slate-100 mb-4">Program</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-1" for="start_time">Začiatok *</label>
                            <input id="start_time" name="start_time" type="time" step="60" class="{{ $input }}" value="{{ old('start_time', $conference->start_time) }}" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-300 mb-1" for="presentation_minutes">Dĺžka príspevku (min) *</label>
                            <input id="presentation_minutes" name="presentation_minutes" type="number" min="1" max="480" class="{{ $input }}" value="{{ old('presentation_minutes', $conference->presentation_minutes) }}" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-300 mb-1" for="break_minutes">Dĺžka prestávky (min)</label>
                            <input id="break_minutes" name="break_minutes" type="number" min="0" max="480" class="{{ $input }}" value="{{ old('break_minutes', $conference->break_minutes) }}">
                        </div>
                    </div>
                </section>
            @else
                <p class="text-sm text-slate-400">
                    Začiatok programu ({{ $conference->startTimeLabel() }}) a dĺžky príspevkov sa menia v časti
                    <span class="text-slate-200">Program → Nastaviť dĺžku príspevkov</span>, kde sa zároveň prepočítajú časy.
                </p>
            @endif
        </div>

        <div class="px-6 py-4 border-t border-slate-800 bg-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('admin.conferences.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
                Zrušiť
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg transition-colors">
                {{ $isNew ? 'Vytvoriť' : 'Uložiť' }}
            </button>
        </div>
    </form>
</div>
@endsection
