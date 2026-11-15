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
    <h2>Rýchle info</h2>
    <img class="infoIllustration" src="{{ asset('images/' . $illustration) }}" alt="{{ $illustrationAlt }}" loading="lazy"/>
    <p><b>Organizátor:</b> <a href="https://www.fri.uniza.sk" target="_blank">Fakulta riadenia a informatiky</a> Žilinská univerzita v Žiline</p>
    <p><b>Forma:</b> hybridná (prezenčne aj online)</p>
    <p><b>Adresa:</b> Veľký diel 3323, 010&nbsp;26 Žilina (<a href="https://maps.app.goo.gl/PaeifyvpnXGbSTbi6" target="_blank">Kde nás nájdete</a>)</p>
    <p><b>Dátum konania:</b> {{ $conference->dateLabel() }}</p>
    @if($contributionDeadline)
        <p><b>Deadline na príspevky:</b> {{ $contributionDeadline }}</p>
    @endif
    @if($deadline)
        <p><b>Deadline na registráciu:</b> {{ $deadline }}</p>
    @endif
    @if($conference->teams_url)
        <p><b>Online pripojenie (MS Teams):</b> <a href="{{ $conference->teams_url }}" target="_blank" rel="noopener noreferrer">Otvoriť míting</a></p>
    @endif

    {{ $slot }}

    <p><b>Kontakt:</b> <a href="mailto:konferenciaAI@fri.uniza.sk">konferenciaAI@fri.uniza.sk</a></p>
</aside>
