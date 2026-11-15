@props(['conference', 'title', 'sub' => null, 'stamp' => false])
@php
    /** @var \App\Models\Conference $conference */
    $deadline = $conference->isRegistrationOpen() ? \App\Models\Conference::formatDate($conference->registration_deadline) : null;
    $contributionDeadline = $conference->contribution_deadline && $conference->isContributionOpen()
        ? \App\Models\Conference::formatDate($conference->contributionDeadline())
        : null;
    $showRegister = $conference->isRegistrationOpen() && ! request()->routeIs('registration');
@endphp
{{-- Page header: FRI logo, site navigation and a full-width hero with title, key facts and actions --}}
<a href="{{ route('home') }}" class="siteHeaderLink">
    <div class="siteHeader">
        <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
    </div>
</a>

<x-site.nav :conference="$conference" />

<header class="hero">
    <div class="heroMain">
        <div class="heroHeading">
            <h1>{!! $title !!}</h1>
            @if($sub)
                <p class="sub">{{ $sub }}</p>
            @endif
            @if($stamp)
                <div class="editionStamp" aria-label="{{ $conference->editionNumber() }}. ročník {{ $conference->year }}">
                    <span class="editionStampMain">{{ $conference->editionNumber() }}. ročník</span>
                    <span class="editionStampYear">{{ $conference->year }}</span>
                </div>
            @endif
        </div>

        <ul class="heroFacts">
            <li><span aria-hidden="true">📅</span> {{ $conference->dateLabel() }}</li>
            <li><span aria-hidden="true">📍</span> FRI UNIZA, Žilina</li>
            <li><span aria-hidden="true">🌐</span> prezenčne aj online</li>
        </ul>

        @if($showRegister || $conference->teams_url || $contributionDeadline || $deadline)
            <div class="heroActions">
                @if($showRegister)
                    <a href="{{ route('registration') }}" class="btn">📝 Registrovať sa</a>
                @endif
                @if($conference->teams_url)
                    <a href="{{ $conference->teams_url }}" class="btn secondary" target="_blank" rel="noopener noreferrer">🎧 Pripojiť sa na MS Teams</a>
                @endif
                @if($contributionDeadline)
                    <span class="pill">Príspevky do {{ $contributionDeadline }}</span>
                @endif
                @if($deadline)
                    <span class="pill">Registrácia do {{ $deadline }}</span>
                @endif
            </div>
        @endif
    </div>
</header>
