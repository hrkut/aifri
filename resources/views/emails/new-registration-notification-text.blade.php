@php($isTalk = $registration->participation_type === 'presentation')
NOVÁ REGISTRÁCIA – Konferencia o AI vo vzdelávaní, {{ $conference->editionLabel() }} ({{ $conference->dateLabel() }})

Typ: {{ $isTalk ? 'aktívna účasť s príspevkom' : 'pasívna účasť' }}
Prihlášok v ročníku {{ $conference->year }}: {{ $totalRegistrations }}

ÚČASTNÍK
Meno: {{ $registration->fullName() }}
E-mail: {{ $registration->email }}
Telefón: {{ $registration->phone ?: '—' }}
Inštitúcia / organizácia: {{ $registration->institution }}
Pozícia / študijný program: {{ $registration->position ?: '—' }}
Forma: {{ $registration->online_participation ? 'Online' : 'Prezenčne' }}
@if($isTalk)

PRÍSPEVOK
Názov: {{ $registration->title ?: '(bez názvu)' }}
Kľúčové slová: {{ $registration->keywords ?: '—' }}
Preferovaný blok: {{ $registration->blockLabel() ?? '—' }}
Abstrakt:
{{ $registration->abstract }}
@endif
@if($registration->notes)

POZNÁMKY
{{ $registration->notes }}
@endif

Detail v administrácii: {{ route('admin.registration.show', ['registration' => $registration->id, 'return' => 'dashboard']) }}
Prijaté: {{ local_time($registration->created_at)?->format('j. n. Y H:i') ?? '—' }}
