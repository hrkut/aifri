@props(['conference'])
@php
    /** @var \App\Models\Conference $conference */
    // [route name, label, short label for phones, icon, visible, routes that count as "current"]
    $items = [
        ['home', 'Úvod', 'Úvod', '🏠', true, 'home'],
        ['program', 'Program', 'Program', '📅', true, 'program'],
        ['registration', 'Registrácia', 'Registrácia', '📝', $conference->isRegistrationOpen(), 'registration'],
        ['question', 'Položiť otázku', 'Otázka', '❓', true, 'question'],
        ['recordings.login', 'Záznamy z minulých ročníkov', 'Záznamy', '📺', true, 'recordings.*'],
        ['committee', 'Výbor', 'Výbor', '👥', true, 'committee'],
    ];
@endphp
{{-- Site navigation: a sticky text bar on wide screens, a grid of all items on phones;
     the current page is highlighted --}}
<nav class="siteNav" aria-label="{{ __('Navigácia') }}">
    <ul>
        @foreach($items as [$route, $label, $short, $icon, $visible, $current])
            @if($visible)
                <li>
                    <a href="{{ lroute($route) }}" @if(request()->routeIs($current, 'en.' . $current)) aria-current="page" @endif>
                        <span class="navIcon" aria-hidden="true">{{ $icon }}</span>
                        <span class="navLabel">{{ __($label) }}</span>
                        <span class="navLabelShort">{{ __($short) }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
