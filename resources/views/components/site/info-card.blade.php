@props([
    'conference',
    'illustration' => 'bulb.png',
    'illustrationAlt' => 'Ilustrácia: žiarovka (nápady a inovácie)',
])
@php
    /** @var \App\Models\Conference $conference */
    $deadline = $conference->isRegistrationOpen() ? \App\Models\Conference::formatDate($conference->registration_deadline) : null;
    $contributionDeadline = $conference->contribution_deadline && $conference->isContributionOpen()
        ? \App\Models\Conference::formatDate($conference->contributionDeadline())
        : null;
@endphp
{{-- "Rýchle info" panel: the same key facts on every public page; page-specific lines go into the slot --}}
<aside class="infoPanel">
    <h2>{{ __('Rýchle info') }}</h2>
    <img class="infoIllustration" src="{{ asset('images/' . $illustration) }}" alt="{{ __($illustrationAlt) }}" loading="lazy"/>
    <p><b>{{ __('Organizátor:') }}</b> <a href="https://www.fri.uniza.sk" target="_blank">{{ __('Fakulta riadenia a informatiky') }}</a>, {{ __('Žilinská univerzita v Žiline') }}</p>
    <p><b>{{ __('Forma:') }}</b> {{ __('hybridná (prezenčne aj online)') }}</p>
    <p><b>{{ __('Adresa:') }}</b> Veľký diel 3323, 010&nbsp;26 Žilina{{ app()->getLocale() === 'en' ? ', Slovakia' : '' }} (<a href="https://maps.app.goo.gl/PaeifyvpnXGbSTbi6" target="_blank">{{ __('Kde nás nájdete') }}</a>)</p>
    <p><b>{{ __('Dátum konania:') }}</b> {{ $conference->dateLabel() }}</p>
    @if($contributionDeadline)
        <p><b>{{ __('Termín na príspevky:') }}</b> {{ $contributionDeadline }}</p>
    @endif
    @if($deadline)
        <p><b>{{ __('Termín registrácie:') }}</b> {{ $deadline }}</p>
    @endif
    @if($conference->teams_url)
        <p><b>{{ __('Online pripojenie (MS Teams):') }}</b> <a href="{{ $conference->teams_url }}" target="_blank" rel="noopener noreferrer">{{ __('Pripojiť sa') }}</a></p>
    @endif

    {{ $slot }}

    <p><b>{{ __('Kontakt:') }}</b> <a href="mailto:konferenciaAI@fri.uniza.sk">konferenciaAI@fri.uniza.sk</a></p>
</aside>
