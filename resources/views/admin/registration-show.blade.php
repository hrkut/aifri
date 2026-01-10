@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">Detaily registrácie</h1>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Späť
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-slate-800">
            <h2 class="text-xl font-semibold text-slate-100">{{ $registration->name }}</h2>
            <p class="text-sm text-slate-400 mt-1">{{ $registration->email }}</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Základné údaje -->
            <section>
                <h3 class="text-lg font-semibold text-slate-100 mb-4 pb-2 border-b border-slate-800">Základné údaje</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Meno a priezvisko</p>
                        <p class="text-slate-200">{{ $registration->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">E-mail</p>
                        <p class="text-slate-200">
                            <a href="mailto:{{ $registration->email }}" class="text-blue-400 hover:text-blue-300">{{ $registration->email }}</a>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Telefón</p>
                        <p class="text-slate-200">{{ $registration->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Inštitúcia / Organizácia</p>
                        <p class="text-slate-200">{{ $registration->institution }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pozícia / Študijný program</p>
                        <p class="text-slate-200">{{ $registration->position ?? '—' }}</p>
                    </div>
                </div>
            </section>

            <!-- Typ účasti -->
            <section>
                <h3 class="text-lg font-semibold text-slate-100 mb-4 pb-2 border-b border-slate-800">Typ účasti</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Typ účasti</p>
                        @if($registration->participation_type === 'presentation')
                            <span class="inline-block px-3 py-1 bg-blue-900 text-blue-200 rounded-lg text-sm font-medium">Aktívna účasť – prednáška</span>
                        @else
                            <span class="inline-block px-3 py-1 bg-gray-700 text-gray-200 rounded-lg text-sm font-medium">Pasívna účasť (poslucháč)</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Forma účasti</p>
                        @if($registration->online_participation)
                            <span class="inline-block px-3 py-1 bg-emerald-900 text-emerald-200 rounded-lg text-sm font-medium">Online</span>
                        @else
                            <span class="inline-block px-3 py-1 bg-slate-700 text-slate-200 rounded-lg text-sm font-medium">Prezenčne</span>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Informácie o príspevku (ak je prezentácia) -->
            @if($registration->participation_type === 'presentation')
            <section>
                <h3 class="text-lg font-semibold text-slate-100 mb-4 pb-2 border-b border-slate-800">Informácie o príspevku</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Názov príspevku</p>
                        <p class="text-slate-200">{{ $registration->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Abstrakt</p>
                        <p class="text-slate-200 whitespace-pre-wrap leading-relaxed">{{ $registration->abstract }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Klúčové slová</p>
                        @if($registration->keywords)
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $registration->keywords) as $keyword)
                                    <span class="px-3 py-1 bg-slate-800 text-slate-300 rounded-lg text-sm">{{ trim($keyword) }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Preferovaný blok</p>
                        <p class="text-slate-200">
                            @switch($registration->block)
                                @case('intro')
                                    Úvodný blok – Vízia a trendy
                                    @break
                                @case('teaching')
                                    Blok 1 – AI vo výučbe a výskume
                                    @break
                                @case('practice')
                                    Blok 2 – AI v praxi
                                    @break
                                @case('students')
                                    Blok 3 – AI očami študentov
                                    @break
                                @default
                                    —
                            @endswitch
                        </p>
                    </div>
                </div>
            </section>
            @endif

            <!-- Doplňujúce informácie -->
            <section>
                <h3 class="text-lg font-semibold text-slate-100 mb-4 pb-2 border-b border-slate-800">Doplňujúce informácie</h3>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Poznámky</p>
                    @if($registration->notes)
                        <p class="text-slate-200 whitespace-pre-wrap leading-relaxed">{{ $registration->notes }}</p>
                    @else
                        <p class="text-slate-400">—</p>
                    @endif
                </div>
            </section>

            <!-- Metadáta -->
            <section>
                <h3 class="text-lg font-semibold text-slate-100 mb-4 pb-2 border-b border-slate-800">Metadáta</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">ID registrácie</p>
                        <p class="text-slate-200 font-mono">{{ $registration->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Vytvorené</p>
                        <p class="text-slate-200">{{ $registration->created_at->format('d.m.Y H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Posledná zmena</p>
                        <p class="text-slate-200">{{ $registration->updated_at->format('d.m.Y H:i:s') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

