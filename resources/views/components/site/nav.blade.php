@props(['conference'])
@php
    /** @var \App\Models\Conference $conference */
    // [route name, label, icon, visible, routes that count as "current"]
    $items = [
        ['home', 'Úvod', '🏠', true, 'home'],
        ['program', 'Program', '📅', true, 'program'],
        ['registration', 'Registrácia', '📝', $conference->isRegistrationOpen(), 'registration'],
        ['question', 'Položiť otázku', '❓', true, 'question'],
        ['recordings.login', 'Záznamy z minulých ročníkov', '📺', true, 'recordings.*'],
        ['committee', 'Výbor', '👥', true, 'committee'],
    ];
@endphp
{{-- Horizontal site navigation (sticks to the top while scrolling); the current page is highlighted --}}
<nav class="siteNav" aria-label="Navigácia">
    <ul>
        @foreach($items as [$route, $label, $icon, $visible, $current])
            @if($visible)
                <li>
                    <a href="{{ route($route) }}" @if(request()->routeIs($current)) aria-current="page" @endif>
                        <span aria-hidden="true">{{ $icon }}</span> {{ $label }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
<script>
    // On narrow screens the bar scrolls sideways: bring the current page into view.
    (function () {
        var list = document.currentScript.previousElementSibling.querySelector('ul');
        var current = list && list.querySelector('[aria-current]');
        if (current && list.scrollWidth > list.clientWidth) {
            list.scrollLeft = current.parentElement.offsetLeft - 8;
        }
    })();
</script>
