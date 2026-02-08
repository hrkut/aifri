@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-slate-100">Pridať príspevok</h1>
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

    <form method="POST" action="{{ route('admin.program.store') }}" class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Autor</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="name">Meno *</label>
                        <input id="name" name="name" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('name') }}" required>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="email">E-mail *</label>
                        <input id="email" name="email" type="email" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('email') }}" required>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="phone">Telefón</label>
                        <input id="phone" name="phone" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('phone') }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="institution">Inštitúcia *</label>
                        <input id="institution" name="institution" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('institution') }}" required>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-slate-100 mb-4">Príspevok</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="time_start">Čas začiatku</label>
                        <input id="time_start" name="time_start" type="time" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('time_start') }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-1" for="block">Blok</label>
                        @php($val = old('block'))
                        <select id="block" name="block" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2">
                            <option value="" {{ $val === null || $val === '' ? 'selected' : '' }}>—</option>
                            <option value="intro" {{ $val === 'intro' ? 'selected' : '' }}>Úvodný blok – Vízia a trendy</option>
                            <option value="teaching" {{ $val === 'teaching' ? 'selected' : '' }}>Blok 1 – AI vo výučbe a vo výskume</option>
                            <option value="practice" {{ $val === 'practice' ? 'selected' : '' }}>Blok 2 – AI v praxi</option>
                            <option value="students" {{ $val === 'students' ? 'selected' : '' }}>Blok 3 – AI očami študentov</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="title">Názov príspevku *</label>
                        <input id="title" name="title" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('title') }}" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="abstract">Abstrakt</label>
                        <textarea id="abstract" name="abstract" rows="6" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2">{{ old('abstract') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-1" for="keywords">Kľúčové slová</label>
                        <input id="keywords" name="keywords" type="text" class="w-full rounded-md bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2" value="{{ old('keywords') }}">
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

