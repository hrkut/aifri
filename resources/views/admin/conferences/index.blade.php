@extends('layouts.admin')

@section('content')
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Conference> $conferences */
    /** @var \App\Models\Conference $working */
@endphp

<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-slate-100">Ročníky konferencie</h1>
            <p class="mt-1 text-sm text-slate-400">
                Zverejnený ročník sa zobrazuje na webe. Registrácie, program a otázky v administrácii patria ročníku, s ktorým práve pracujete.
            </p>
        </div>
        <a href="{{ route('admin.conferences.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg transition-colors whitespace-nowrap">
            + Nový ročník
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-800 text-slate-300 text-left">
                <tr>
                    <th class="px-4 py-3">Ročník</th>
                    <th class="px-4 py-3">Dátum</th>
                    <th class="px-4 py-3">Deadline registrácie</th>
                    <th class="px-4 py-3 text-right">Prihlášky</th>
                    <th class="px-4 py-3 text-right">Otázky</th>
                    <th class="px-4 py-3">Stav</th>
                    <th class="px-4 py-3 text-right">Akcie</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach ($conferences as $c)
                    <tr class="{{ $c->id === $working->id ? 'bg-slate-800/40' : '' }}">
                        <td class="px-4 py-3 font-semibold text-slate-100">{{ $c->year }}</td>
                        <td class="px-4 py-3 text-slate-200 whitespace-nowrap">{{ $c->date ? $c->dateLabel() : '—' }}</td>
                        <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ \App\Models\Conference::formatDate($c->registration_deadline) ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">{{ $c->registrations_count }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">{{ $c->questions_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @if ($c->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-900/60 text-emerald-200 border border-emerald-800">Zverejnený</span>
                                @endif
                                @if ($c->isReadOnly())
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-slate-800 text-slate-300 border border-slate-700">Archív</span>
                                @endif
                                @if ($c->id === $working->id)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-sky-900/60 text-sky-200 border border-sky-800">Pracujete s ním</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if ($c->id !== $working->id)
                                    <form method="POST" action="{{ route('admin.conferences.select', $c) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-md bg-slate-700 hover:bg-slate-600 text-slate-100 whitespace-nowrap">{{ $c->isReadOnly() ? 'Prezerať' : 'Pracovať s ním' }}</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.conferences.edit', $c) }}" class="px-3 py-1.5 rounded-md bg-slate-700 hover:bg-slate-600 text-slate-100">Upraviť</a>
                                @if (! $c->is_active)
                                    <form method="POST" action="{{ route('admin.conferences.activate', $c) }}"
                                          onsubmit="return confirm('Zverejniť ročník {{ $c->year }} na webe? Verejné stránky (dátum, registrácia, program, otázky) sa prepnú na tento ročník.');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-md bg-amber-700 hover:bg-amber-600 text-white">Zverejniť</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
