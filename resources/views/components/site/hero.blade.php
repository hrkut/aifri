@props(['conference', 'title', 'sub' => null, 'stamp' => false])
@php
    /** @var \App\Models\Conference $conference */
    $deadline = $conference->isRegistrationOpen() ? \App\Models\Conference::formatDate($conference->registration_deadline) : null;
    $contributionDeadline = $conference->contribution_deadline && $conference->isContributionOpen()
        ? \App\Models\Conference::formatDate($conference->contributionDeadline())
        : null;
    $showRegister = $conference->isRegistrationOpen() && ! request()->routeIs('registration', 'en.registration');
@endphp
{{-- Page header: FRI logo, site navigation, then the title set directly on the circuit background --}}
<div class="siteTop">
    <a href="{{ lroute('home') }}" class="siteHeaderLink">
        <div class="siteHeader">
            <img src="{{ asset('images/FRI_logo.png') }}" alt="{{ __('Logo FRI') }}" class="friLogo" loading="lazy"/>
        </div>
    </a>

    {{-- Language switcher: the same page in the other language --}}
    <nav class="langSwitch" aria-label="{{ __('Jazyk') }}">
        @foreach(['sk' => 'Slovenská verzia', 'en' => 'English version'] as $code => $label){{-- labels stay in their own language --}}
            @if(app()->getLocale() === $code)
                <span aria-current="true" lang="{{ $code }}">{{ strtoupper($code) }}</span>
            @else
                <a href="{{ locale_url($code) }}" hreflang="{{ $code }}" lang="{{ $code }}" title="{{ $label }}">{{ strtoupper($code) }}</a>
            @endif
        @endforeach
    </nav>
</div>

<x-site.nav :conference="$conference" />

<header class="hero">
    <div class="heroHeading">
        <h1>{!! $title !!}</h1>
        @if($sub)
            <p class="sub">{{ $sub }}</p>
        @endif
        @if($stamp)
            <div class="editionStamp" aria-label="{{ $conference->editionLabel() }} {{ $conference->year }}">
                <span class="editionStampMain">{{ $conference->editionLabel() }}</span>
                <span class="editionStampYear">{{ $conference->year }}</span>
            </div>
        @endif
    </div>

    <dl class="heroFacts">
        <div>
            <dt>{{ __('Dátum konania') }}</dt>
            <dd>{{ $conference->dateLabel() }}</dd>
        </div>
        <div>
            <dt>{{ __('Miesto') }}</dt>
            <dd>{{ __('FRI UNIZA, Žilina') }}</dd>
        </div>
        <div>
            <dt>{{ __('Forma') }}</dt>
            <dd>{{ __('prezenčne aj online') }}</dd>
        </div>
    </dl>

    @if($showRegister || $conference->teams_url || $contributionDeadline || $deadline)
        <div class="heroActions">
            @if($showRegister)
                <a href="{{ lroute('registration') }}" class="btn">{{ __('Registrovať sa') }} <svg class="btnIcon" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 4.5 12 8l-3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            @endif
            @if($conference->teams_url)
                <a href="{{ $conference->teams_url }}" class="btn secondary" target="_blank" rel="noopener noreferrer">{{ __('Pripojiť sa na MS Teams') }} <svg class="btnIcon" viewBox="0 0 16 16" aria-hidden="true"><path d="M6.5 3.5H3.5v9h9v-3M9 3h4v4M13 3 7.5 8.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            @endif
            @if($contributionDeadline || $deadline)
                <ul class="heroDeadlines">
                    @if($contributionDeadline)
                        <li>{{ __('Príspevky do :date', ['date' => $contributionDeadline]) }}</li>
                    @endif
                    @if($deadline)
                        <li>{{ __('Registrácia do :date', ['date' => $deadline]) }}</li>
                    @endif
                </ul>
            @endif
        </div>
    @endif
</header>
