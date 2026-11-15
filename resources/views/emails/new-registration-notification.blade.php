@extends('emails.layout')

@php
    /** @var \App\Models\Registration $registration */
    /** @var \App\Models\Conference $conference */
    /** @var int $totalRegistrations */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $isTalk = $registration->participation_type === 'presentation';
    $p = 'margin: 0 0 14px; font-family: ' . $font . '; font-size: 16px; line-height: 1.6; color: ' . $c['text'] . ';';
    $link = 'color: ' . $c['link'] . '; text-decoration: none;';
    $pre = 'margin: 0; white-space: pre-wrap; font-family: ' . $font . '; font-size: 15px; line-height: 1.6; color: ' . $c['text'] . ';';
@endphp

@section('title', 'Nová registrácia')
@section('preheader', $registration->fullName() . ' (' . $registration->institution . ') sa prihlásil(a) na konferenciu.')
@section('heading', '🆕 Nová registrácia')

@section('content')
    <p style="{!! $p !!} margin-bottom: 22px;">
        Na konferenciu {{ $conference->dateLabel() }} prišla nová registrácia
        – <strong>{{ $isTalk ? 'aktívna účasť s príspevkom' : 'pasívna účasť' }}</strong>.
        Ročník {{ $conference->year }} má teraz <strong>{{ $totalRegistrations }}</strong> {{ $totalRegistrations === 1 ? 'prihlášku' : ($totalRegistrations < 5 ? 'prihlášky' : 'prihlášok') }}.
    </p>

    <x-email.panel title="Účastník" :highlight="true">
        <div style="font-size: 17px; font-weight: 700; padding-bottom: 4px;">{{ $registration->fullName() }}</div>
        <div><strong>E-mail:</strong> <a href="mailto:{{ $registration->email }}" style="{!! $link !!}">{{ $registration->email }}</a></div>
        <div><strong>Telefón:</strong> {{ $registration->phone ?: '—' }}</div>
        <div><strong>Inštitúcia / organizácia:</strong> {{ $registration->institution }}</div>
        <div><strong>Pozícia / študijný program:</strong> {{ $registration->position ?: '—' }}</div>
    </x-email.panel>

    <x-email.panel title="Účasť">
        <div><strong>Typ:</strong> {{ $isTalk ? 'Aktívna – prednáška' : 'Pasívna (poslucháč)' }}</div>
        <div><strong>Forma:</strong> {{ $registration->online_participation ? 'Online' : 'Prezenčne' }}</div>
    </x-email.panel>

    @if($isTalk)
        <x-email.panel title="Príspevok">
            <div style="font-weight: 700; padding-bottom: 6px;">{{ $registration->title ?: '(bez názvu)' }}</div>
            @if($registration->abstract)
                <p style="{!! $pre !!} color: {{ $c['muted'] }}; padding-bottom: 8px;">{{ $registration->abstract }}</p>
            @endif
            <div><strong>Kľúčové slová:</strong> {{ $registration->keywords ?: '—' }}</div>
            <div><strong>Preferovaný blok:</strong> {{ $registration->blockLabel() ?? '—' }}</div>
        </x-email.panel>
    @endif

    @if($registration->notes)
        <x-email.panel title="Poznámky">
            <p style="{!! $pre !!}">{{ $registration->notes }}</p>
        </x-email.panel>
    @endif

    <x-email.button :href="route('admin.registration.show', ['registration' => $registration->id, 'return' => 'dashboard'])">Otvoriť v administrácii</x-email.button>

    <p style="{!! $p !!} font-size: 13px; color: {{ $c['muted'] }}; margin: 0;">
        Prijaté {{ local_time($registration->created_at)?->format('j. n. Y \o H:i') ?? '—' }}.
        Odpoveďou na tento e-mail napíšete priamo účastníkovi.
    </p>
@endsection
