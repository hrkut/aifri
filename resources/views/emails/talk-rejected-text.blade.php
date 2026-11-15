@php($fullName = $registration->fullName())
KONFERENCIA O UMELEJ INTELIGENCII VO VZDELÁVANÍ – {{ $conference->editionNumber() }}. ročník ({{ $conference->year }})

Príspevok neprijatý

Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},

ďakujeme, že ste prihlásili príspevok na Konferenciu o umelej inteligencii vo vzdelávaní. Programový výbor sa po posúdení rozhodol váš príspevok tentoraz nezaradiť do programu.

VÁŠ PRÍSPEVOK
{{ $registration->title }}
@if($registration->review_comment)

ZDÔVODNENIE PROGRAMOVÉHO VÝBORU
{{ $registration->review_comment }}
@endif

Na konferenciu {{ $conference->dateLabel() }} ste naďalej srdečne pozvaní ako poslucháč, vaša registrácia zostáva platná. Ak máte k rozhodnutiu otázky, odpovedzte prosím na tento e-mail.

S pozdravom
programový výbor konferencie

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ url('/') }}
