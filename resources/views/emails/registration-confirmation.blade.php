@extends('emails.layout')

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

@section('title', 'Potvrdenie registrácie')
@section('preheader', 'Vašu registráciu na konferenciu ' . $conference->dateLabel() . ' sme zaznamenali a potvrdzujeme.')
@section('heading', '✅ Registrácia potvrdená')

@section('content')
    <p style="{!! $p !!}">Dobrý deň{{ $fullName !== '' ? ', ' . $fullName : '' }},</p>
    <p style="{!! $p !!} margin-bottom: 22px;">
        ďakujeme za prihlásenie na Konferenciu o umelej inteligencii vo vzdelávaní.
        @if($isTalk)
            Vašu registráciu ako <strong>aktívneho účastníka s&nbsp;príspevkom</strong> sme zaznamenali a&nbsp;týmto ju <strong>potvrdzujeme</strong>.
        @else
            Vašu registráciu ako <strong>pasívneho účastníka</strong> sme zaznamenali a&nbsp;týmto ju <strong>potvrdzujeme</strong>.
        @endif
    </p>
    @if($isTalk)
        <p style="{!! $p !!} margin-bottom: 22px;">
            Príspevok teraz posúdi programový výbor. O&nbsp;výsledku a&nbsp;zaradení do programu vás budeme informovať e-mailom{{ $notification ? ' do ' . $notification : '' }}.
        </p>
    @endif

    <x-email.panel title="Kedy a kde" :highlight="true">
        <div><strong>Dátum konania:</strong> {{ $conference->dateLabel() }}</div>
        <div><strong>Miesto:</strong> Fakulta riadenia a informatiky, Žilinská univerzita v&nbsp;Žiline, Veľký diel 3323, 010&nbsp;26 Žilina
            (<a href="{{ \App\Mail\EmailTheme::MAP_URL }}" style="color: {{ $c['link'] }}; text-decoration: underline;">mapa</a>)</div>
        <div><strong>Forma:</strong> hybridná (prezenčne aj online)</div>
        @if($conference->teams_url)
            <div><strong>Online pripojenie:</strong> <a href="{{ $conference->teams_url }}" style="color: {{ $c['link'] }}; text-decoration: underline;">MS Teams míting</a></div>
        @else
            <div style="color: {{ $c['muted'] }};">Odkaz na online pripojenie (MS Teams) zverejníme pred konferenciou na webe.</div>
        @endif
    </x-email.panel>

    @if($isTalk)
        <x-email.panel title="Váš príspevok">
            <div style="font-weight: 700; padding-bottom: 4px;">{{ $registration->title }}</div>
            @if($registration->blockLabel())
                <div><strong>Preferovaný blok:</strong> {{ $registration->blockLabel() }}</div>
            @endif
            @if($registration->keywords)
                <div><strong>Kľúčové slová:</strong> {{ $registration->keywords }}</div>
            @endif
            <div><strong>Dĺžka prezentácie:</strong> max. {{ $conference->presentation_minutes ?: 15 }} minút</div>
        </x-email.panel>
    @endif

    <x-email.panel title="Vaše údaje">
        <div><strong>Meno:</strong> {{ $fullName }}</div>
        <div><strong>E-mail:</strong> {{ $registration->email }}</div>
        <div><strong>Inštitúcia:</strong> {{ $registration->institution }}</div>
        @if($registration->position)
            <div><strong>Pozícia:</strong> {{ $registration->position }}</div>
        @endif
        <div><strong>Forma účasti:</strong> {{ $registration->online_participation ? 'online' : 'prezenčne' }}</div>
    </x-email.panel>

    <x-email.button :href="route('program')">📅 Program konferencie</x-email.button>

    <p style="{!! $p !!} font-size: 15px; color: {{ $c['muted'] }};">
        Ak ste sa prihlásili omylom alebo sa nebudete môcť zúčastniť, dajte nám prosím vedieť odpoveďou na tento e-mail.
    </p>
    <p style="{!! $p !!} font-size: 15px; margin: 18px 0 0;">
        Tešíme sa na vás,<br>
        <strong>organizačný výbor konferencie</strong>
    </p>
@endsection
