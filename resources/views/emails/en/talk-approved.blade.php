@extends('emails.layout')

{{-- English version of emails/talk-approved.blade.php --}}
@php
    /** @var \App\Models\Registration $registration */
    /** @var \App\Models\Conference $conference */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $fullName = $registration->fullName();
    $minutes = (int) ($registration->duration_minutes ?: $conference->presentation_minutes ?: 15);
    $p = 'margin: 0 0 14px; font-family: ' . $font . '; font-size: 16px; line-height: 1.6; color: ' . $c['text'] . ';';
@endphp

@section('title', 'Talk accepted')
@section('preheader', 'Your talk “' . $registration->title . '” has been accepted for the conference programme.')
@section('heading', '✅ Talk accepted')

@section('content')
    <p style="{!! $p !!}">Dear {{ $fullName !== '' ? $fullName : 'author' }},</p>
    <p style="{!! $p !!} margin-bottom: 22px;">
        we are pleased to let you know that the programme committee has <strong>accepted your talk</strong> for the programme
        of the Conference on Artificial Intelligence in Education.
    </p>

    <x-email.panel title="Your talk" :highlight="true">
        <div style="font-weight: 700; padding-bottom: 4px;">{{ $registration->title }}</div>
        @if($registration->blockLabel())
            <div><strong>Block:</strong> {{ __($registration->blockLabel()) }}</div>
        @endif
        <div><strong>Length of the talk:</strong> max. {{ $minutes }} minutes</div>
    </x-email.panel>

    <x-email.panel title="When and where">
        <div><strong>Date:</strong> {{ $conference->dateLabel() }}</div>
        <div><strong>Venue:</strong> Faculty of Management Science and Informatics, University of Žilina, Veľký diel 3323, 010&nbsp;26 Žilina, Slovakia
            (<a href="{{ \App\Mail\EmailTheme::MAP_URL }}" style="color: {{ $c['link'] }}; text-decoration: underline;">map</a>)</div>
        <div><strong>Your participation:</strong> {{ $registration->online_participation ? 'online' : 'on site' }}</div>
        @if($conference->teams_url)
            <div><strong>Online:</strong> <a href="{{ $conference->teams_url }}" style="color: {{ $c['link'] }}; text-decoration: underline;">MS Teams meeting</a></div>
        @endif
    </x-email.panel>

    <x-email.button :href="lroute('program')">📅 Conference programme</x-email.button>

    <p style="{!! $p !!} font-size: 15px; color: {{ $c['muted'] }};">
        We will let you know the time of your talk once the programme is put together; it will also be published on the conference website.
        If you cannot attend, please let us know by replying to this e-mail.
    </p>
    <p style="{!! $p !!} font-size: 15px; margin: 18px 0 0;">
        We look forward to your talk,<br>
        <strong>the conference programme committee</strong>
    </p>
@endsection
