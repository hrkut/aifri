@php($fullName = $registration->fullName())
KONFERENCIA O UMELEJ INTELIGENCII VO VZDELÁVANÍ – {{ $conference->editionNumber() }}. ročník ({{ $conference->year }})

Registrácia potvrdená

Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},

ďakujeme za prihlásenie na Konferenciu o umelej inteligencii vo vzdelávaní. Vašu registráciu ako pasívneho účastníka sme zaznamenali a týmto ju potvrdzujeme.

KEDY A KDE
Dátum: {{ $conference->dateLabel() }}
Miesto: Fakulta riadenia a informatiky, Žilinská univerzita v Žiline, Veľký diel 3323, 010 26 Žilina
Forma: hybridná (prezenčne aj online)
@if($conference->teams_url)
Online pripojenie (MS Teams): {{ $conference->teams_url }}
@else
Odkaz na online pripojenie (MS Teams) zverejníme pred konferenciou na webe.
@endif

VAŠE ÚDAJE
Meno: {{ $fullName }}
E-mail: {{ $registration->email }}
Inštitúcia: {{ $registration->institution }}
@if($registration->position)
Pozícia: {{ $registration->position }}
@endif

Program konferencie: {{ route('program') }}

Ak ste sa prihlásili omylom alebo sa nebudete môcť zúčastniť, dajte nám prosím vedieť odpoveďou na tento e-mail.

Tešíme sa na Vás,
organizačný výbor konferencie

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ url('/') }}
