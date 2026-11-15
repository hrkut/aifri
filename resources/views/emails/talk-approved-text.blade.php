@php
    $fullName = $registration->fullName();
    $minutes = (int) ($registration->duration_minutes ?: $conference->presentation_minutes ?: 15);
@endphp
KONFERENCIA O UMELEJ INTELIGENCII VO VZDELÁVANÍ – {{ $conference->editionNumber() }}. ročník ({{ $conference->year }})

Príspevok prijatý

Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},

s potešením vám oznamujeme, že programový výbor prijal váš príspevok do programu Konferencie o umelej inteligencii vo vzdelávaní.

VÁŠ PRÍSPEVOK
Názov: {{ $registration->title }}
@if($registration->blockLabel())
Blok: {{ $registration->blockLabel() }}
@endif
Dĺžka prezentácie: max. {{ $minutes }} minút

KEDY A KDE
Dátum konania: {{ $conference->dateLabel() }}
Miesto: Fakulta riadenia a informatiky, Žilinská univerzita v Žiline, Veľký diel 3323, 010 26 Žilina
Forma vašej účasti: {{ $registration->online_participation ? 'online' : 'prezenčne' }}
@if($conference->teams_url)
Online pripojenie (MS Teams): {{ $conference->teams_url }}
@endif

Program konferencie: {{ route('program') }}

Čas vášho vystúpenia vám oznámime po zostavení programu, program potom zverejníme aj na webe konferencie. Ak sa nebudete môcť zúčastniť, dajte nám prosím vedieť odpoveďou na tento e-mail.

Tešíme sa na vaše vystúpenie,
programový výbor konferencie

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ url('/') }}
