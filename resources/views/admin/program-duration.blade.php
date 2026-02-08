@extends('layouts.admin')

@section('content')
@php
    /** @var \App\Models\Conference|null $conference */
    $conference = $conference ?? null;

    $startTime = old('start_time', $conference?->start_time ?? '09:00');
    if (is_string($startTime) && $startTime !== '') {
        try {
            $startTime = \Illuminate\Support\Carbon::parse($startTime)->format('H:i');
        } catch (\Throwable $e) {
            // keep
        }
    }
@endphp

<div class="max-w-3xl mx-auto" x-data="{ confirmOpen: false }" x-on:keydown.escape.window="confirmOpen = false">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">Nastaviť dĺžku príspevku</h1>
        <a href="{{ route('admin.program') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
            Späť
        </a>
    </div>

    <!-- Confirm modal -->
    <div
        x-cloak
        x-show="confirmOpen"
        class="fixed inset-0 z-50 flex items-center justify-center"
        aria-modal="true"
        role="dialog"
        x-transition.opacity.duration.150ms
    >
        <div class="absolute inset-0 bg-black/60" x-on:click="confirmOpen = false"></div>

        <div
            class="relative w-full max-w-md mx-4 rounded-xl bg-slate-900 border border-slate-800 shadow-xl"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="p-5">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-full bg-amber-900/40 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.29A2 2 0 005 19h14a2 2 0 001.81-2.85l-7.1-12.29a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-slate-100">Potvrdenie akcie</h2>
                        <p class="mt-1 text-sm text-slate-300">
                            Táto akcia <strong>zresetuje všetky časy</strong> príspevkov aj prestávok a nanovo ich prepočíta podľa aktuálneho poradia v programe.
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-slate-800 bg-slate-800/60 flex items-center justify-end gap-3">
                <button type="button" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100" x-on:click="confirmOpen = false">
                    Zrušiť
                </button>
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-amber-700 hover:bg-amber-600 text-white"
                    x-on:click="document.getElementById('program-duration-form')?.submit()"
                >
                    Potvrdiť a prepočítať
                </button>
            </div>
        </div>
    </div>

    <div class="mb-6 p-4 bg-amber-950/30 border border-amber-900 rounded-lg text-amber-100">
        <p class="font-semibold">Upozornenie</p>
        <p class="text-sm mt-1">Pred nastavením dĺžky sa <strong>zresetujú všetky časy</strong> príspevkov aj prestávok a nanovo sa prepočítajú podľa aktuálneho poradia v programe.</p>
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

    <form id="program-duration-form" method="POST" action="{{ route('admin.program.duration.apply') }}" class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Nastavenia</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="start_time">Začiatok programu *</label>
                        <input id="start_time" name="start_time" type="time" step="60" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ $startTime }}" required>
                        <p class="text-xs text-slate-400 mt-1">Od tohto času začne prvá položka programu.</p>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="presentation_minutes">Dĺžka príspevku (min) *</label>
                        <input id="presentation_minutes" name="presentation_minutes" type="number" min="1" max="480" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('presentation_minutes', $conference?->presentation_minutes ?? 15) }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="break_minutes">Dĺžka prestávky (min)</label>
                        <input id="break_minutes" name="break_minutes" type="number" min="0" max="480" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('break_minutes', $conference?->break_minutes ?? 20) }}">
                        <p class="text-xs text-slate-400 mt-1">Ak je 0, prestávky sa nebudú automaticky posúvať o dĺžku (zostanú iba ako položky v programe s časom začiatku).</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="px-6 py-4 border-t border-slate-800 bg-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('admin.program') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
                Zrušiť
            </a>
            <button type="button" class="inline-flex items-center px-4 py-2 bg-amber-700 hover:bg-amber-600 text-white rounded-lg transition-colors" x-on:click="confirmOpen = true">
                Nastaviť a prepočítať
            </button>
        </div>
    </form>
</div>
@endsection

