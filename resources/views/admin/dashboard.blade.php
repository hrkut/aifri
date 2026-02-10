@extends('layouts.admin')

@section('content')
@php
    /** @var string $sort */
    /** @var string $direction */
    /** @var string $q */
    /** @var string $participationTypeFilter */
    /** @var string $onlineParticipationFilter */

    $nextDir = fn (string $col) => ($sort ?? '') === $col && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';

    $sortUrl = function (string $col) use ($nextDir) {
        return request()->fullUrlWithQuery([
            'sort' => $col,
            'direction' => $nextDir($col),
            'page' => 1,
        ]);
    };

    $sortIndicator = function (string $col) {
        if (($GLOBALS['sort'] ?? null) !== $col) return '';
        return (($GLOBALS['direction'] ?? 'asc') === 'asc') ? ' ▲' : ' ▼';
    };
@endphp

<div class="mx-auto w-[90vw] max-w-none">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-3xl font-semibold text-slate-100">Registrácie</h1>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="hidden" name="sort" value="{{ $sort ?? 'created_at' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'desc' }}">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <label class="text-sm text-slate-200 select-none flex items-center gap-2">
                    <span class="whitespace-nowrap">Typ účasti</span>
                    <select
                        name="participation_type"
                        class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        onchange="this.form.submit()"
                    >
                        <option value="" @selected(($participationTypeFilter ?? '') === '')>Všetky</option>
                        <option value="active" @selected(($participationTypeFilter ?? '') === 'active')>Aktívna</option>
                        <option value="passive" @selected(($participationTypeFilter ?? '') === 'passive')>Pasívna</option>
                    </select>
                </label>

                <label class="text-sm text-slate-200 select-none flex items-center gap-2">
                    <span class="whitespace-nowrap">Forma účasti</span>
                    <select
                        name="online_participation"
                        class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        onchange="this.form.submit()"
                    >
                        <option value="" @selected(($onlineParticipationFilter ?? '') === '')>Všetky</option>
                        <option value="in_person" @selected(($onlineParticipationFilter ?? '') === 'in_person')>Prezenčne</option>
                        <option value="online" @selected(($onlineParticipationFilter ?? '') === 'online')>Online</option>
                    </select>
                </label>


                <div class="relative w-full sm:w-96">
                    <input
                        type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Hľadať (meno, e-mail, inštitúcia)"
                        class="w-full px-3 py-2 pr-10 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500"
                    />

                    <button
                        type="button"
                        class="absolute inset-y-0 right-2 flex items-center justify-center w-7 h-7 my-auto rounded-full border border-slate-600 bg-slate-800/60 text-slate-200 hover:bg-slate-700/70 hover:text-white transition-colors {{ empty($q) ? 'hidden' : '' }}"
                        aria-label="Vymazať hľadanie"
                        title="Vymazať"
                        onclick="(function(btn){const form=btn.form; if(!form) return; const input=form.querySelector('input[name=\'q\']'); if(!input) return; input.value=''; form.submit();})(this)"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 6L14 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M14 6L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 font-medium">
                    Hľadať
                </button>

                <a
                    href="{{ route('admin.registrations.export', request()->except('page')) }}"
                    class="px-4 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white font-medium"
                >
                    Export
                </a>

                @php
                    $attendanceEnabled = (($onlineParticipationFilter ?? '') === 'in_person');
                @endphp

                @if($attendanceEnabled)
                    <a
                        href="{{ route('admin.registrations.attendance_pdf', request()->except('page')) }}"
                        class="px-4 py-2 rounded-lg bg-indigo-700 hover:bg-indigo-600 text-white font-medium"
                        title="Prezenčná listina (PDF)"
                    >
                        Prezenčná listina (PDF)
                    </a>
                @else
                    <span
                        class="px-4 py-2 rounded-lg bg-indigo-950/40 text-indigo-200/50 font-medium border border-indigo-900/40 cursor-not-allowed select-none"
                        title="Prezenčná listina je dostupná len pri filtre Forma účasti: Prezenčne"
                        aria-disabled="true"
                    >
                        Prezenčná listina (PDF)
                    </span>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-max min-w-full divide-y divide-slate-800">
                <thead class="bg-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $sortUrl('name') }}" class="hover:text-slate-100 whitespace-nowrap">Meno{!! (($sort ?? '') === 'name') ? (($direction ?? 'asc') === 'asc' ? ' ▲' : ' ▼') : '' !!}</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $sortUrl('email') }}" class="hover:text-slate-100 whitespace-nowrap">Email{!! (($sort ?? '') === 'email') ? (($direction ?? 'asc') === 'asc' ? ' ▲' : ' ▼') : '' !!}</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap min-w-[140px]">
                            <a href="{{ $sortUrl('participation_type') }}" class="hover:text-slate-100 whitespace-nowrap">Typ účasti{!! (($sort ?? '') === 'participation_type') ? (($direction ?? 'asc') === 'asc' ? ' ▲' : ' ▼') : '' !!}</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap min-w-[160px]">
                            <a href="{{ $sortUrl('online_participation') }}" class="hover:text-slate-100 whitespace-nowrap">Forma účasti{!! (($sort ?? '') === 'online_participation') ? (($direction ?? 'asc') === 'asc' ? ' ▲' : ' ▼') : '' !!}</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap">
                            <a href="{{ $sortUrl('institution') }}" class="hover:text-slate-100 whitespace-nowrap">Inštitúcia{!! (($sort ?? '') === 'institution') ? (($direction ?? 'asc') === 'asc' ? ' ▲' : ' ▼') : '' !!}</a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap min-w-[160px]">
                            <a href="{{ $sortUrl('created_at') }}" class="hover:text-slate-100 whitespace-nowrap">Vytvorené{!! (($sort ?? '') === 'created_at') ? (($direction ?? 'asc') === 'asc' ? ' ▲' : ' ▼') : '' !!}</a>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap">Akcia</th>
                    </tr>
                </thead>
                <tbody class="bg-slate-900 divide-y divide-slate-800">
                    @forelse($registrations as $registration)
                        <tr class="hover:bg-slate-800 transition-colors">
                            <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->name }}</td>
                            <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->email }}</td>
                            <td class="px-6 py-3 text-sm text-slate-200 whitespace-nowrap">
                                {{ $registration->participation_type === 'presentation' ? 'Aktívna' : 'Pasívna' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-200 whitespace-nowrap">
                                {{ $registration->online_participation ? 'Online' : 'Prezenčne' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-200">{{ $registration->institution }}</td>
                            <td class="px-6 py-3 text-sm text-slate-200 whitespace-nowrap">{{ $registration->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-3 text-center">
                                <a href="{{ route('admin.registration.show', ['registration' => $registration->id, 'return' => 'dashboard']) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors" title="Zobraziť detaily">
                                    <svg class="w-4 h-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-sm text-slate-500">
                                Žiadne registrácie.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $registrations->links() }}
    </div>
</div>
@endsection
