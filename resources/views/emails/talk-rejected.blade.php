@extends('emails.layout')

@php
    /** @var \App\Models\Registration $registration */
    /** @var \App\Models\Conference $conference */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $fullName = $registration->fullName();
    $p = 'margin: 0 0 14px; font-family: ' . $font . '; font-size: 16px; line-height: 1.6; color: ' . $c['text'] . ';';
    $pre = 'margin: 0; white-space: pre-wrap; font-family: ' . $font . '; font-size: 15px; line-height: 1.6; color: ' . $c['text'] . ';';
@endphp

@section('title', 'Príspevok neprijatý')
@section('preheader', 'Výsledok posúdenia príspevku „' . $registration->title . '“.')
@section('heading', '❌ Príspevok neprijatý')

@section('content')
    <p style="{!! $p !!}">Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},</p>
    <p style="{!! $p !!} margin-bottom: 22px;">
        ďakujeme, že ste poslali príspevok na Konferenciu o umelej inteligencii vo vzdelávaní.
        Programový výbor sa po posúdení rozhodol váš príspevok <strong>tentoraz nezaradiť do programu</strong>.
    </p>

    <x-email.panel title="Váš príspevok">
        <div style="font-weight: 700;">{{ $registration->title }}</div>
    </x-email.panel>

    @if($registration->review_comment)
        <x-email.panel title="Zdôvodnenie programového výboru" :highlight="true">
            <p style="{!! $pre !!}">{{ $registration->review_comment }}</p>
        </x-email.panel>
    @endif

    <p style="{!! $p !!} font-size: 15px;">
        Na konferenciu {{ $conference->dateLabel() }} ste naďalej srdečne pozvaní ako poslucháč, vaša registrácia zostáva platná.
        Ak máte k rozhodnutiu otázky, odpovedzte prosím na tento e-mail.
    </p>
    <p style="{!! $p !!} font-size: 15px; margin: 18px 0 0;">
        S pozdravom<br>
        <strong>programový výbor konferencie</strong>
    </p>
@endsection
