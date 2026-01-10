@php
/** @var \App\Models\Registration $registration */
@endphp

<h2>Nová registrácia na konferenciu</h2>

<p><b>Meno:</b> {{ $registration->name }}</p>
<p><b>E-mail:</b> {{ $registration->email }}</p>
<p><b>Telefón:</b> {{ $registration->phone ?? '—' }}</p>
<p><b>Inštitúcia / Organizácia:</b> {{ $registration->institution }}</p>
<p><b>Pozícia / Študijný program:</b> {{ $registration->position ?? '—' }}</p>

<p><b>Typ účasti:</b>
    @if($registration->participation_type === 'presentation')
        Aktívna účasť – prednáška
    @else
        Pasívna účasť (poslucháč)
    @endif
</p>

<p><b>Forma účasti:</b> {{ $registration->online_participation ? 'Online' : 'Prezenčne' }}</p>

@if($registration->participation_type === 'presentation')
    <hr>
    <p><b>Názov príspevku:</b> {{ $registration->title }}</p>
    <p><b>Abstrakt:</b></p>
    <pre style="white-space: pre-wrap; font-family: inherit;">{{ $registration->abstract }}</pre>
    <p><b>Klúčové slová:</b> {{ $registration->keywords ?? '—' }}</p>
    <p><b>Preferovaný blok:</b> {{ $registration->block ?? '—' }}</p>
@endif

@if($registration->notes)
    <hr>
    <p><b>Poznámky:</b></p>
    <pre style="white-space: pre-wrap; font-family: inherit;">{{ $registration->notes }}</pre>
@endif

<hr>
<p style="font-size: 12px; color: #666;">
    Vytvorené: {{ optional($registration->created_at)->format('d.m.Y H:i:s') ?? '—' }}
</p>

