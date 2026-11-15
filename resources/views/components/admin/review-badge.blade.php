@props(['registration'])
@php
    /** @var \App\Models\Registration $registration */
    $status = $registration->reviewStatus();
    $styles = [
        \App\Models\Registration::REVIEW_PENDING => 'bg-amber-900/50 text-amber-200 border-amber-700/60',
        \App\Models\Registration::REVIEW_APPROVED => 'bg-emerald-900/50 text-emerald-200 border-emerald-700/60',
        \App\Models\Registration::REVIEW_REJECTED => 'bg-red-900/50 text-red-200 border-red-700/60',
    ];
@endphp
{{-- Review state of a talk; passive participants have none --}}
@if($status)
    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-xs font-medium {{ $styles[$status] }}"
          @if($registration->reviewed_at) title="Posúdené {{ local_time($registration->reviewed_at)->format('j. n. Y H:i') }}" @endif>
        {{ $registration->reviewLabel() }}
    </span>
@else
    <span class="text-slate-500">—</span>
@endif
