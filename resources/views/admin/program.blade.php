@extends('layouts.admin')

@section('content')
<div
    class="mx-auto w-[90vw] max-w-none"
    x-data="{ confirmOpen: false, confirmFormId: null }"
    x-on:keydown.escape.window="confirmOpen = false"
>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">Program (aktívne príspevky)</h1>
        <div class="flex items-center gap-3">
            @php
                $total = is_object($registrations) && method_exists($registrations, 'total') ? $registrations->total() : $registrations->count();
            @endphp

            <span class="text-sm text-slate-300">Počet príspevkov: <span class="font-semibold">{{ $total }}</span></span>

            <a href="{{ route('admin.program.duration') }}" class="inline-flex items-center px-4 py-2 bg-amber-700 hover:bg-amber-600 text-white rounded-lg transition-colors">
                Nastaviť dĺžku príspevkov
            </a>
            <a href="{{ route('admin.program.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg transition-colors">
                Pridať príspevok
            </a>
            <a href="{{ route('admin.breaks.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-100 rounded-lg transition-colors">
                Pridať prestávku
            </a>
        </div>
    </div>

    <!-- Confirm delete modal -->
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
                    <div class="shrink-0 w-10 h-10 rounded-full bg-red-900/40 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.29A2 2 0 005 19h14a2 2 0 001.81-2.85l-7.1-12.29a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-slate-100">Vymazať príspevok</h2>
                        <p class="mt-1 text-sm text-slate-300">Naozaj chcete vymazať tento príspevok? Táto akcia sa nedá vrátiť späť.</p>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-slate-800 bg-slate-800/60 flex items-center justify-end gap-3">
                <button type="button" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100" x-on:click="confirmOpen = false">
                    Zrušiť
                </button>
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-600 text-white"
                    x-on:click="if(confirmFormId){ document.getElementById(confirmFormId)?.submit(); }"
                >
                    Vymazať
                </button>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider" title="Presunúť">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Čas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Trvanie (min)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Meno</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Inštitúcia</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Názov príspevku</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Blok</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-slate-800"
                    data-program-sortable="true"
                    data-reorder-url="{{ route('admin.program.reorder') }}"
                >
                    @forelse($registrations as $registration)
                        @php($deleteFormId = 'delete-program-' . $registration->id)
                        @php($isBreak = $registration->participation_type === 'break')
                        @php($timeVal = $registration->time_start ? \Illuminate\Support\Carbon::parse($registration->time_start)->format('H:i') : '')
                        @php($durVal = (int) ($registration->duration_minutes ?? 0))

                        @if($isBreak)
                            <tr class="bg-slate-800/40 hover:bg-slate-800 transition-colors" data-id="{{ $registration->id }}">
                                <td class="px-3 py-3 text-sm text-slate-300">
                                    <button type="button" data-drag-handle="true" class="cursor-move" title="Presunúť">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-200 font-mono whitespace-nowrap">
                                    <span data-program-time-input data-id="{{ $registration->id }}">{{ $timeVal ?: '—' }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-200 font-mono whitespace-nowrap">
                                    <input
                                        type="number"
                                        min="0"
                                        max="480"
                                        class="w-[90px] rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-2 py-1"
                                        value="{{ $durVal }}"
                                        data-program-duration-input
                                        data-id="{{ $registration->id }}"
                                    >
                                </td>
                                <td colspan="5" class="px-6 py-3 text-sm text-slate-100 font-semibold">
                                    {{ $registration->title ?? 'Prestávka' }}
                                    @if($registration->notes)
                                        <div class="mt-1 text-xs font-normal text-slate-300">{{ $registration->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a
                                            href="{{ route('admin.breaks.edit', ['break' => $registration->id]) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-700 hover:bg-blue-600 transition-colors"
                                            title="Upraviť prestávku"
                                        >
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.program.destroy', $registration) }}" id="{{ $deleteFormId }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-700 hover:bg-red-600 transition-colors"
                                                title="Vymazať prestávku"
                                                x-on:click="confirmFormId = '{{ $deleteFormId }}'; confirmOpen = true"
                                            >
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr class="hover:bg-slate-800 transition-colors" data-id="{{ $registration->id }}">
                                <td class="px-3 py-3 text-sm text-slate-300">
                                    <button type="button" data-drag-handle="true" class="cursor-move" title="Presunúť">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-200 font-mono whitespace-nowrap">
                                    <span data-program-time-input data-id="{{ $registration->id }}">{{ $timeVal ?: '—' }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-200 font-mono whitespace-nowrap">
                                    <input
                                        type="number"
                                        min="0"
                                        max="480"
                                        class="w-[90px] rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-2 py-1"
                                        value="{{ $durVal }}"
                                        data-program-duration-input
                                        data-id="{{ $registration->id }}"
                                    >
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-200 font-mono">{{ $registration->id }}</td>
                                <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->name }}</td>
                                <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->institution }}</td>
                                <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->title ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->block ?? '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a
                                            href="{{ route('admin.registration.edit', ['registration' => $registration->id, 'return' => 'program']) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-700 hover:bg-blue-600 transition-colors"
                                            title="Upraviť"
                                        >
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>

                                        <a
                                            href="{{ route('admin.registration.show', ['registration' => $registration->id, 'return' => 'program']) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors"
                                            title="Zobraziť detaily"
                                        >
                                            <svg class="w-4 h-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.program.destroy', $registration) }}" id="{{ $deleteFormId }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-700 hover:bg-red-600 transition-colors"
                                                title="Vymazať"
                                                x-on:click="confirmFormId = '{{ $deleteFormId }}'; confirmOpen = true"
                                            >
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-6 text-center text-sm text-slate-500">
                                Žiadne aktívne registrácie.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Time inline edit is handled in resources/js/app.js to avoid double-binding and bugs --}}
</div>
@endsection

