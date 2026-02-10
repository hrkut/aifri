@extends('layouts.admin')

@section('content')
@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $questions */
@endphp

<div class="mx-auto w-[90vw] max-w-none">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-3xl font-semibold text-slate-100">Otázky</h1>

        <!-- Search Filter and Refresh Button on one row -->
        <form method="GET" action="{{ route('admin.questions') }}" class="flex gap-2 items-center flex-wrap sm:flex-nowrap">
            <div class="relative w-full sm:w-96">
                <input
                    type="text"
                    name="q"
                    value="{{ request()->query('q', '') }}"
                    placeholder="Hľadať (meno, otázka)"
                    class="w-full px-3 py-2 pr-10 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500"
                />
                @if (request()->query('q'))
                    <button
                        type="button"
                        class="absolute inset-y-0 right-2 flex items-center justify-center w-7 h-7 my-auto rounded-full border border-slate-600 bg-slate-800/60 text-slate-200 hover:bg-slate-700/70 hover:text-white transition-colors"
                        aria-label="Vymazať hľadanie"
                        title="Vymazať"
                        onclick="(function(btn){const form=btn.form; if(!form) return; const input=form.querySelector('input[name=\'q\']'); if(!input) return; input.value=''; form.submit();})(this)"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 6L14 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M14 6L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                @endif
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 font-medium whitespace-nowrap">
                Hľadať
            </button>
            <button
                type="button"
                onclick="location.reload()"
                class="px-4 py-2 rounded-lg bg-indigo-700 hover:bg-indigo-600 text-white font-medium whitespace-nowrap transition"
                title="Obnoviť otázky"
            >
                Obnoviť
            </button>
        </form>
    </div>

    <script>
        // Auto-refresh every 10 seconds
        setInterval(() => {
            location.reload();
        }, 10000);
    </script>

    @if ($questions->isEmpty())
        <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-8 text-center">
            <p class="text-slate-300">Zatiaľ žiadne otázky.</p>
        </div>
    @else
        <div class="bg-slate-800/30 border border-slate-700 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/80 border-b border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-slate-200">ID</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-200">Meno</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-200">Otázka</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-200">Vytvorená</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-200">Akcia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach ($questions as $question)
                            <tr class="hover:bg-slate-800/30 transition {{ $question->answered ? 'opacity-50' : '' }}">
                                <td class="px-6 py-4 {{ $question->answered ? 'text-slate-500' : 'text-slate-300' }}">{{ $question->id }}</td>
                                <td class="px-6 py-4 {{ $question->answered ? 'text-slate-500' : 'text-slate-200' }} font-medium whitespace-nowrap">{{ $question->name }}</td>
                                <td class="px-6 py-4 {{ $question->answered ? 'text-slate-500' : 'text-slate-300' }} whitespace-normal break-words">
                                    {{ $question->question }}
                                </td>
                                <td class="px-6 py-4 {{ $question->answered ? 'text-slate-500' : 'text-slate-400' }} whitespace-nowrap">
                                    {{ $question->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if (!$question->answered)
                                        <form action="{{ route('admin.questions.mark_answered', $question) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-900/30 hover:bg-emerald-900/60 text-emerald-400 hover:text-emerald-300 transition" title="Označiť ako zodpovedanú">
                                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill="currentColor"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-slate-500" title="Označená ako zodpovedaná">
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill="currentColor"/>
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $questions->links() }}
        </div>
    @endif
</div>
@endsection

