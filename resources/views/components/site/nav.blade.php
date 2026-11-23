@props(['conference'])
@php
    /** @var \App\Models\Conference $conference */
    // [route name, label, short label for phones, routes that count as "current"]
    $items = [
        ['home', 'Úvod', 'Úvod', 'home'],
        ['program', 'Program', 'Program', 'program'],
        ['question', 'Položiť otázku', 'Otázka', 'question'],
        ['recordings.login', 'Záznamy z minulých ročníkov', 'Záznamy', 'recordings.*'],
        ['committee', 'Výbor', 'Výbor', 'committee'],
    ];
    $isCurrent = fn (string $pattern) => request()->routeIs($pattern, 'en.' . $pattern);
@endphp
{{-- Site navigation: text links with the current page highlighted, registration as the
     call to action on the right (only while it is open); a grid of all items on phones --}}
<nav class="siteNav" aria-label="{{ __('Navigácia') }}">
    <ul>
        @foreach($items as [$route, $label, $short, $current])
            <li>
                <a href="{{ lroute($route) }}" @if($isCurrent($current)) aria-current="page" @endif>
                    <span class="navLabel">{{ __($label) }}</span>
                    <span class="navLabelShort">{{ __($short) }}</span>
                </a>
            </li>
        @endforeach
        @if($conference->isRegistrationOpen())
            <li class="navCta">
                <a href="{{ lroute('registration') }}" @if($isCurrent('registration')) aria-current="page" @endif>
                    {{ __('Registrovať sa') }}
                    <svg class="navArrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 4.5 12 8l-3.5 3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </li>
        @endif
    </ul>
</nav>
