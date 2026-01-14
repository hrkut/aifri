@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-semibold text-slate-100 mb-6">Registrácie</h1>
    <div class="bg-slate-900 border border-slate-800 rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800">
                <thead class="bg-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Meno</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Typ účasti</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Forma účasti</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Inštitúcia</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Vytvorené</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider">Akcia</th>
                    </tr>
                </thead>
                <tbody class="bg-slate-900 divide-y divide-slate-800">
                    @forelse($registrations as $registration)
                        <tr class="hover:bg-slate-800 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-200">{{ $registration->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-200">{{ $registration->email }}</td>
                            <td class="px-4 py-3 text-sm text-slate-200">
                                {{ $registration->participation_type === 'presentation' ? 'Aktívna' : 'Pasívna' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-200">
                                {{ $registration->online_participation ? 'Online' : 'Prezenčne' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-200">{{ $registration->institution }}</td>
                            <td class="px-4 py-3 text-sm text-slate-200">{{ $registration->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.registration.show', $registration->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors" title="Zobraziť detaily">
                                    <svg class="w-4 h-4 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">Žiadne registrácie.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-800 bg-slate-800">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection
