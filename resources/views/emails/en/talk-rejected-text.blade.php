@php($fullName = $registration->fullName())
CONFERENCE ON ARTIFICIAL INTELLIGENCE IN EDUCATION – {{ $conference->editionLabel() }} ({{ $conference->year }})

Talk not accepted

Dear {{ $fullName !== '' ? $fullName : 'author' }},

thank you for submitting a talk to the Conference on Artificial Intelligence in Education. After review, the programme committee has decided not to include your talk in the programme this time.

YOUR TALK
{{ $registration->title }}
@if($registration->review_comment)

REASON GIVEN BY THE PROGRAMME COMMITTEE
{{ $registration->review_comment }}
@endif

You are still very welcome to attend the conference on {{ $conference->dateLabel() }} as a listener; your registration remains valid. If you have any questions about the decision, please reply to this e-mail.

Kind regards,
the conference programme committee

{{ \App\Mail\EmailTheme::CONTACT_EMAIL }} · {{ lroute('home') }}
