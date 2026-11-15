@php
    $fullName = $registration->fullName();
    $minutes = (int) ($registration->duration_minutes ?: $conference->presentation_minutes ?: 15);
@endphp
CONFERENCE ON ARTIFICIAL INTELLIGENCE IN EDUCATION – {{ $conference->editionLabel() }} ({{ $conference->year }})

Talk accepted

Dear {{ $fullName !== '' ? $fullName : 'author' }},

we are pleased to let you know that the programme committee has accepted your talk for the programme of the Conference on Artificial Intelligence in Education.

YOUR TALK
Title: {{ $registration->title }}
@if($registration->blockLabel())
Block: {{ __($registration->blockLabel()) }}
@endif
Length of the talk: max. {{ $minutes }} minutes

WHEN AND WHERE
Date: {{ $conference->dateLabel() }}
Venue: Faculty of Management Science and Informatics, University of Žilina, Veľký diel 3323, 010 26 Žilina, Slovakia
Your participation: {{ $registration->online_participation ? 'online' : 'on site' }}
@if($conference->teams_url)
Online (MS Teams): {{ $conference->teams_url }}
@endif

Conference programme: {{ lroute('program') }}

We will let you know the time of your talk once the programme is put together; it will also be published on the conference website. If you cannot attend, please let us know by replying to this e-mail.

We look forward to your talk,
the conference programme committee

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ lroute('home') }}
