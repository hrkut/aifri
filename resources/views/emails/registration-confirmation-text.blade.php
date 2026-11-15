@php($fullName = $registration->fullName())
@php($isTalk = $registration->participation_type === 'presentation')
@php($notification = \App\Models\Conference::formatDate($conference->notification_date))
KONFERENCIA O UMELEJ INTELIGENCII VO VZDELÁVANÍ – {{ $conference->editionLabel() }} ({{ $conference->year }})

Registrácia potvrdená

Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},

ďakujeme za registráciu na Konferenciu o umelej inteligencii vo vzdelávaní. Vašu registráciu ako {{ $isTalk ? 'aktívneho účastníka s príspevkom' : 'pasívneho účastníka' }} sme zaznamenali a týmto ju potvrdzujeme.
@if($isTalk)

Príspevok teraz posúdi programový výbor. O výsledku a zaradení do programu vás budeme informovať e-mailom{{ $notification ? ' do ' . $notification : '' }}.

VÁŠ PRÍSPEVOK
Názov: {{ $registration->title }}
@if($registration->blockLabel())
Preferovaný blok: {{ $registration->blockLabel() }}
@endif
@if($registration->keywords)
Kľúčové slová: {{ $registration->keywords }}
@endif
Dĺžka prezentácie: max. {{ $conference->presentation_minutes ?: 15 }} minút
@endif

KEDY A KDE
Dátum konania: {{ $conference->dateLabel() }}
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
Forma účasti: {{ $registration->online_participation ? 'online' : 'prezenčne' }}

Program konferencie: {{ route('program') }}

Ak ste sa zaregistrovali omylom alebo sa nebudete môcť zúčastniť, dajte nám prosím vedieť odpoveďou na tento e-mail.

Tešíme sa na vás,
organizačný výbor konferencie

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ url('/') }}
