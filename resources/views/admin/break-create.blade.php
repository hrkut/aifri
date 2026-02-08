@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">Pridať prestávku</h1>
        <a href="{{ route('admin.program') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
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

    <form method="POST" action="{{ route('admin.breaks.store') }}" class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Prestávka</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="time_start">Čas začiatku *</label>
                        <input id="time_start" name="time_start" type="time" step="60" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('time_start') }}" required>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="title">Názov / popis *</label>
                        <input id="title" name="title" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('title', 'Prestávka') }}" required>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="duration_minutes">Dĺžka (minúty) *</label>
                        <input id="duration_minutes" name="duration_minutes" type="number" min="1" max="480" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('duration_minutes', $conference->break_minutes ?? 20) }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="notes">Poznámka</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <div class="px-6 py-4 border-t border-slate-800 bg-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('admin.program') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">
                Zrušiť
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-lg transition-colors">
                Pridať
            </button>
        </div>
    </form>
</div>
@endsection

