@props(['conference'])
@php
    /** @var \App\Models\Conference $conference */
    $deadline = $conference->isRegistrationOpen() ? \App\Models\Conference::formatDate($conference->registration_deadline) : null;
    $contributionDeadline = $conference->contribution_deadline && $conference->isContributionOpen()
        ? \App\Models\Conference::formatDate($conference->contributionDeadline())
        : null;
@endphp
{{-- Same navigation on every public page; the link to the current page is left out --}}
<nav class="card loginCard" aria-label="Navigácia">
    <h2>Navigácia</h2>
    @unless(request()->routeIs('home'))
        <p><a href="{{ route('home') }}">🏠 Úvodná stránka</a></p>
    @endunless
    @unless(request()->routeIs('program'))
        <p><a href="{{ route('program') }}">📅 Program konferencie</a></p>
    @endunless
    @if($conference->isRegistrationOpen() && ! request()->routeIs('registration'))
        <p><a href="{{ route('registration') }}">📝 Registrácia na konferenciu</a></p>
    @endif
    @if($conference->teams_url)
        <p><a href="{{ $conference->teams_url }}" target="_blank" rel="noopener noreferrer">🎧 Pripojiť sa na MS Teams</a></p>
    @endif
    @unless(request()->routeIs('question'))
        <p><a href="{{ route('question') }}">❓ Položiť otázku</a></p>
    @endunless
    @unless(request()->routeIs('recordings.*'))
        <p><a href="{{ route('recordings.login') }}">📺 Záznamy z minulých ročníkov</a></p>
    @endunless

    @if($contributionDeadline || $deadline)
        <div class="pills">
            @if($contributionDeadline)
                <span class="pill">Príspevky do {{ $contributionDeadline }}</span>
            @endif
            @if($deadline)
                <span class="pill">Registrácia do {{ $deadline }}</span>
            @endif
        </div>
    @endif

    <hr>
    @unless(request()->routeIs('committee'))
        <p><a href="{{ route('committee') }}">👥 Programový a organizačný výbor</a></p>
    @endunless
</nav>
