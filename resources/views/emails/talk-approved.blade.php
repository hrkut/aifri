@extends('emails.layout')

@php
    /** @var \App\Models\Registration $registration */
    /** @var \App\Models\Conference $conference */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $fullName = $registration->fullName();
    $minutes = (int) ($registration->duration_minutes ?: $conference->presentation_minutes ?: 15);
    $p = 'margin: 0 0 14px; font-family: ' . $font . '; font-size: 16px; line-height: 1.6; color: ' . $c['text'] . ';';
@endphp

@section('title', 'Príspevok prijatý')
@section('preheader', 'Váš príspevok „' . $registration->title . '“ bol prijatý do programu konferencie.')
@section('heading', '✅ Príspevok prijatý')

@section('content')
    <p style="{!! $p !!}">Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},</p>
    <p style="{!! $p !!} margin-bottom: 22px;">
        s potešením vám oznamujeme, že programový výbor <strong>prijal váš príspevok</strong> do programu
        Konferencie o umelej inteligencii vo vzdelávaní.
    </p>

    <x-email.panel title="Váš príspevok" :highlight="true">
        <div style="font-weight: 700; padding-bottom: 4px;">{{ $registration->title }}</div>
        @if($registration->blockLabel())
            <div><strong>Blok:</strong> {{ $registration->blockLabel() }}</div>
        @endif
        <div><strong>Dĺžka prezentácie:</strong> max. {{ $minutes }} minút</div>
    </x-email.panel>

    <x-email.panel title="Kedy a kde">
        <div><strong>Dátum konania:</strong> {{ $conference->dateLabel() }}</div>
        <div><strong>Miesto:</strong> Fakulta riadenia a informatiky, Žilinská univerzita v&nbsp;Žiline, Veľký diel 3323, 010&nbsp;26 Žilina
            (<a href="{{ \App\Mail\EmailTheme::MAP_URL }}" style="color: {{ $c['link'] }}; text-decoration: underline;">mapa</a>)</div>
        <div><strong>Forma vašej účasti:</strong> {{ $registration->online_participation ? 'online' : 'prezenčne' }}</div>
        @if($conference->teams_url)
            <div><strong>Online pripojenie:</strong> <a href="{{ $conference->teams_url }}" style="color: {{ $c['link'] }}; text-decoration: underline;">MS Teams míting</a></div>
        @endif
    </x-email.panel>

    <x-email.button :href="route('program')">📅 Program konferencie</x-email.button>

    <p style="{!! $p !!} font-size: 15px; color: {{ $c['muted'] }};">
        Čas vášho vystúpenia vám oznámime po zostavení programu, program potom zverejníme aj na webe konferencie.
        Ak sa nebudete môcť zúčastniť, dajte nám prosím vedieť odpoveďou na tento e-mail.
    </p>
    <p style="{!! $p !!} font-size: 15px; margin: 18px 0 0;">
        Tešíme sa na vaše vystúpenie,<br>
        <strong>programový výbor konferencie</strong>
    </p>
@endsection
