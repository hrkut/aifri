@extends('emails.layout')

{{-- English version of emails/talk-rejected.blade.php --}}
@php
    /** @var \App\Models\Registration $registration */
    /** @var \App\Models\Conference $conference */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $fullName = $registration->fullName();
    $p = 'margin: 0 0 14px; font-family: ' . $font . '; font-size: 16px; line-height: 1.6; color: ' . $c['text'] . ';';
    $pre = 'margin: 0; white-space: pre-wrap; font-family: ' . $font . '; font-size: 15px; line-height: 1.6; color: ' . $c['text'] . ';';
@endphp

@section('title', 'Talk not accepted')
@section('preheader', 'Result of the review of your talk “' . $registration->title . '”.')
@section('heading', '❌ Talk not accepted')

@section('content')
    <p style="{!! $p !!}">Dear {{ $fullName !== '' ? $fullName : 'author' }},</p>
    <p style="{!! $p !!} margin-bottom: 22px;">
        thank you for submitting a talk to the Conference on Artificial Intelligence in Education.
        After review, the programme committee has decided <strong>not to include your talk in the programme this time</strong>.
    </p>

    <x-email.panel title="Your talk">
        <div style="font-weight: 700;">{{ $registration->title }}</div>
    </x-email.panel>

    @if($registration->review_comment)
        <x-email.panel title="Reason given by the programme committee" :highlight="true">
            <p style="{!! $pre !!}">{{ $registration->review_comment }}</p>
        </x-email.panel>
    @endif

    <p style="{!! $p !!} font-size: 15px;">
        You are still very welcome to attend the conference on {{ $conference->dateLabel() }} as a listener; your registration remains valid.
        If you have any questions about the decision, please reply to this e-mail.
    </p>
    <p style="{!! $p !!} font-size: 15px; margin: 18px 0 0;">
        Kind regards,<br>
        <strong>the conference programme committee</strong>
    </p>
@endsection
