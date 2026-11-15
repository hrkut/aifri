@props([
    'id',
    'heading',
    'action',                // form action (POST)
    'submitLabel',
    'color' => 'red',        // red | emerald: colour of the confirm button
])
@php
    $submitClasses = [
        'red' => 'bg-red-700 hover:bg-red-600',
        'emerald' => 'bg-emerald-700 hover:bg-emerald-600',
    ][$color] ?? 'bg-red-700 hover:bg-red-600';
@endphp
{{-- Modal confirmation for reviewing a talk (native <dialog>: focus trap, Esc closes) --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title"
        class="m-auto w-full max-w-lg rounded-xl border border-slate-700 bg-slate-900 p-0 text-left text-slate-100 shadow-2xl backdrop:bg-black/70">
    <form method="POST" action="{{ $action }}">
        @csrf
        <div class="p-5">
            <h2 id="{{ $id }}-title" class="text-lg font-semibold text-slate-100">{{ $heading }}</h2>
            {{ $slot }}
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-800 bg-slate-800/60 px-5 py-4">
            <button type="button" class="rounded-lg bg-slate-700 px-4 py-2 text-sm text-slate-100 hover:bg-slate-600"
                    onclick="this.closest('dialog').close()">
                Zrušiť
            </button>
            <button type="submit" class="rounded-lg px-4 py-2 text-sm font-medium text-white {{ $submitClasses }}">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</dialog>
