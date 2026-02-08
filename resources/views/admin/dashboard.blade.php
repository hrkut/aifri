@extends('layouts.admin')

@section('content')
@php
    /** @var string $sort */
    /** @var string $direction */
    /** @var string $q */
    /** @var bool $filterActive */
    /** @var bool $filterInPerson */

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
                <label class="inline-flex items-center gap-2 text-sm text-slate-200 select-none">
                    <input type="checkbox" name="only_active" value="1" class="rounded border-slate-600 bg-slate-900" @checked(!empty($filterActive)) onchange="this.form.submit()">
                    <span>Typ účasti: Aktívna</span>
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-slate-200 select-none">
                    <input type="checkbox" name="only_in_person" value="1" class="rounded border-slate-600 bg-slate-900" @checked(!empty($filterInPerson)) onchange="this.form.submit()">
                    <span>Forma účasti: Prezenčne</span>
                </label>

                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Hľadať (meno, e-mail, inštitúcia)"
                    class="w-full sm:w-96 px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500"
                />

                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 font-medium">
                    Hľadať
                </button>

                <a
                    href="{{ route('admin.registrations.export', request()->except('page')) }}"
                    class="px-4 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white font-medium"
                >
                    Export
                </a>

                @if(!empty($q) || !empty($filterActive) || !empty($filterInPerson))
                    <a href="{{ route('admin.dashboard', ['sort' => $sort ?? 'created_at', 'direction' => $direction ?? 'desc']) }}" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium">
                        Vymazať
                    </a>
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
                            <td colspan="7" class="px-6 py-6 text-center text-sm text-slate-500">Žiadne registrácie.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-800 bg-slate-800">
            {{ $registrations->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
