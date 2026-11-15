@extends('emails.layout')

{{-- English version of emails/registration-confirmation.blade.php --}}
@php
    /** @var \App\Models\Registration $registration */
    /** @var \App\Models\Conference $conference */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $fullName = $registration->fullName();
    $isTalk = $registration->participation_type === 'presentation';
    $notification = \App\Models\Conference::formatDate($conference->notification_date);
    $p = 'margin: 0 0 14px; font-family: ' . $font . '; font-size: 16px; line-height: 1.6; color: ' . $c['text'] . ';';
@endphp

@section('title', 'Registration confirmed')
@section('preheader', 'We have received and confirm your registration for the conference on ' . $conference->dateLabel() . '.')
@section('heading', '✅ Registration confirmed')

@section('content')
    <p style="{!! $p !!}">Dear {{ $fullName !== '' ? $fullName : 'participant' }},</p>
    <p style="{!! $p !!} margin-bottom: 22px;">
        thank you for registering for the Conference on Artificial Intelligence in Education.
        @if($isTalk)
            We have received your registration as an <strong>active participant with a talk</strong> and hereby <strong>confirm</strong> it.
        @else
            We have received your registration as an <strong>attendee</strong> and hereby <strong>confirm</strong> it.
        @endif
    </p>
    @if($isTalk)
        <p style="{!! $p !!} margin-bottom: 22px;">
            The programme committee will now review your talk. We will let you know the result by e-mail{{ $notification ? ' by ' . $notification : '' }}.
        </p>
    @endif

    <x-email.panel title="When and where" :highlight="true">
        <div><strong>Date:</strong> {{ $conference->dateLabel() }}</div>
        <div><strong>Venue:</strong> Faculty of Management Science and Informatics, University of Žilina, Veľký diel 3323, 010&nbsp;26 Žilina, Slovakia
            (<a href="{{ \App\Mail\EmailTheme::MAP_URL }}" style="color: {{ $c['link'] }}; text-decoration: underline;">map</a>)</div>
        <div><strong>Format:</strong> hybrid (on site and online)</div>
        @if($conference->teams_url)
            <div><strong>Online:</strong> <a href="{{ $conference->teams_url }}" style="color: {{ $c['link'] }}; text-decoration: underline;">MS Teams meeting</a></div>
        @else
            <div style="color: {{ $c['muted'] }};">We will publish the link to join online (MS Teams) on the website before the conference.</div>
        @endif
    </x-email.panel>

    @if($isTalk)
        <x-email.panel title="Your talk">
            <div style="font-weight: 700; padding-bottom: 4px;">{{ $registration->title }}</div>
            @if($registration->blockLabel())
                <div><strong>Preferred block:</strong> {{ __($registration->blockLabel()) }}</div>
            @endif
            @if($registration->keywords)
                <div><strong>Keywords:</strong> {{ $registration->keywords }}</div>
            @endif
            <div><strong>Length of the talk:</strong> max. {{ $conference->presentation_minutes ?: 15 }} minutes</div>
        </x-email.panel>
    @endif

    <x-email.panel title="Your details">
        <div><strong>Name:</strong> {{ $fullName }}</div>
        <div><strong>E-mail:</strong> {{ $registration->email }}</div>
        <div><strong>Institution:</strong> {{ $registration->institution }}</div>
        @if($registration->position)
            <div><strong>Position:</strong> {{ $registration->position }}</div>
        @endif
        <div><strong>Participation:</strong> {{ $registration->online_participation ? 'online' : 'on site' }}</div>
    </x-email.panel>

    <x-email.button :href="lroute('program')">📅 Conference programme</x-email.button>

    <p style="{!! $p !!} font-size: 15px; color: {{ $c['muted'] }};">
        If you registered by mistake or cannot attend, please let us know by replying to this e-mail.
    </p>
    <p style="{!! $p !!} font-size: 15px; margin: 18px 0 0;">
        We look forward to seeing you,<br>
        <strong>the conference organising committee</strong>
    </p>
@endsection
