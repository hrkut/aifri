@php($fullName = $registration->fullName())
@php($isTalk = $registration->participation_type === 'presentation')
@php($notification = \App\Models\Conference::formatDate($conference->notification_date))
CONFERENCE ON ARTIFICIAL INTELLIGENCE IN EDUCATION – {{ $conference->editionLabel() }} ({{ $conference->year }})

Registration confirmed

Dear {{ $fullName !== '' ? $fullName : 'participant' }},

thank you for registering for the Conference on Artificial Intelligence in Education. We have received your registration as {{ $isTalk ? 'an active participant with a talk' : 'an attendee' }} and hereby confirm it.
@if($isTalk)

The programme committee will now review your talk. We will let you know the result by e-mail{{ $notification ? ' by ' . $notification : '' }}.

YOUR TALK
Title: {{ $registration->title }}
@if($registration->blockLabel())
Preferred block: {{ __($registration->blockLabel()) }}
@endif
@if($registration->keywords)
Keywords: {{ $registration->keywords }}
@endif
Length of the talk: max. {{ $conference->presentation_minutes ?: 15 }} minutes
@endif

WHEN AND WHERE
Date: {{ $conference->dateLabel() }}
Venue: Faculty of Management Science and Informatics, University of Žilina, Veľký diel 3323, 010 26 Žilina, Slovakia
Format: hybrid (on site and online)
@if($conference->teams_url)
Online (MS Teams): {{ $conference->teams_url }}
@else
We will publish the link to join online (MS Teams) on the website before the conference.
@endif

YOUR DETAILS
Name: {{ $fullName }}
E-mail: {{ $registration->email }}
Institution: {{ $registration->institution }}
@if($registration->position)
Position: {{ $registration->position }}
@endif
Participation: {{ $registration->online_participation ? 'online' : 'on site' }}

Conference programme: {{ lroute('program') }}

If you registered by mistake or cannot attend, please let us know by replying to this e-mail.

We look forward to seeing you,
the conference organising committee

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ lroute('home') }}
