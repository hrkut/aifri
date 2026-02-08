@extends('layouts.admin')

@section('content')
@php
    $return = request()->query('return');
    $backUrl = match ($return) {
        'dashboard' => route('admin.dashboard'),
        'program' => route('admin.program'),
        default => route('admin.program'),
    };

    $isPresentation = ($registration->participation_type ?? null) === 'presentation';

    $timeStartValue = old('time_start', $registration->time_start);
    if (is_string($timeStartValue) && $timeStartValue !== '') {
        try {
            $timeStartValue = \Illuminate\Support\Carbon::parse($timeStartValue)->format('H:i');
        } catch (\Throwable $e) {
            // keep as-is
        }
    }
@endphp

<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">Upraviť registráciu</h1>
        <a href="{{ $backUrl }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
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

    <form method="POST" action="{{ route('admin.registration.update', ['registration' => $registration->id, 'return' => $return]) }}" class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-6">
            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Základné údaje</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="name">Meno *</label>
                        <input id="name" name="name" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('name', $registration->name) }}" required>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="email">E-mail *</label>
                        <input id="email" name="email" type="email" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('email', $registration->email) }}" required>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="title_before">Titul pred menom</label>
                        <input id="title_before" name="title_before" type="text" maxlength="50" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('title_before', $registration->title_before) }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="title_after">Titul za menom</label>
                        <input id="title_after" name="title_after" type="text" maxlength="50" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('title_after', $registration->title_after) }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="phone">Telefón</label>
                        <input id="phone" name="phone" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('phone', $registration->phone) }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="institution">Inštitúcia *</label>
                        <input id="institution" name="institution" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('institution', $registration->institution) }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1">Forma účasti</label>
                        @php($fp = (string) old('online_participation', ($registration->online_participation === null ? '0' : ($registration->online_participation ? '1' : '0'))))
                        <div class="flex flex-wrap gap-4 mt-2">
                            <label class="inline-flex items-center gap-2 text-slate-200">
                                <input type="radio" name="online_participation" value="0" class="text-emerald-600" {{ $fp === '0' ? 'checked' : '' }}>
                                <span>Prezenčne</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-slate-200">
                                <input type="radio" name="online_participation" value="1" class="text-emerald-600" {{ $fp === '1' ? 'checked' : '' }}>
                                <span>Online</span>
                            </label>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Platí pre príspevky v programe (pre bežné registrácie je informačné).</p>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Príspevok / program</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="time_start">Čas začiatku{{ $isPresentation ? ' *' : '' }}</label>
                        <input id="time_start" name="time_start" type="time" step="60" inputmode="numeric" pattern="^([01]\d|2[0-3]):[0-5]\d$" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ $timeStartValue }}" {{ $isPresentation ? 'required' : '' }}>
                        <p class="text-xs text-slate-400 mt-1">{{ $isPresentation ? 'Povinné pre príspevok v programe. Formát HH:mm.' : 'Voliteľné. Formát HH:mm.' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="block">Blok</label>
                        <select id="block" name="block" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2">
                            @php($val = old('block', $registration->block))
                            <option value="" {{ $val === null || $val === '' ? 'selected' : '' }}>—</option>
                            <option value="intro" {{ $val === 'intro' ? 'selected' : '' }}>Úvodný blok – Vízia a trendy</option>
                            <option value="teaching" {{ $val === 'teaching' ? 'selected' : '' }}>Blok 1 – AI vo výučbe a vo výskume</option>
                            <option value="practice" {{ $val === 'practice' ? 'selected' : '' }}>Blok 2 – AI v praxi</option>
                            <option value="students" {{ $val === 'students' ? 'selected' : '' }}>Blok 3 – AI očami študentov</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="title">Názov príspevku</label>
                        <input id="title" name="title" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('title', $registration->title) }}">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="abstract">Abstrakt</label>
                        <textarea id="abstract" name="abstract" rows="6" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2">{{ old('abstract', $registration->abstract) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="keywords">Kľúčové slová</label>
                        <input id="keywords" name="keywords" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('keywords', $registration->keywords) }}">
                    </div>
                </div>
            </section>
        </div>

        <div class="px-6 py-4 border-t border-slate-800 bg-slate-800 flex items-center justify-end gap-3">
            <a href="{{ $backUrl }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
                Zrušiť
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors">
                Uložiť
            </button>
        </div>
    </form>
</div>
@endsection

