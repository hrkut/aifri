@extends('layouts.public')

@section('title', 'Konferencia o umelej inteligencii vo vzdelávaní')
@section('meta_description', 'Konferencia o umelej inteligencii vo vzdelávaní — AI in education')

@php
    $place = 'Fakulta riadenia a informatiky, Žilinská univerzita v Žiline, Univerzitná 8215/1, 010 26 Žilina';
    $date = '11. 2. 2026 (streda) od 9:00';
    $deadline = 'Už len online do 9. 2. 2026';
    $webUrl = 'https://konferenciaAI.fri.uniza.sk';
@endphp

@section('content')
    <a href="{{ route('home') }}" class="siteHeaderLink">
        <div class="siteHeader">
            <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
        </div>
    </a>

    <header>
        <div style="flex: 1; min-width: 0;">
            <div class="heroTitle">
                <div class="heroText">
                    <div>
                        <h1>KONFERENCIA O UMELEJ INTELIGENCII<br>VO VZDELÁVANÍ</h1>
                        <p class="sub">(Conference on AI in education)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card loginCard" style="flex: 0 0 auto; min-width: 300px;">
            <h2>Informácie</h2>
            <p><a href="{{ route('program') }}">📅 Program konferencie</a></p>
            {{-- <p><a href="{{ route('registration') }}">Registrácia na konferenciu</a></p> --}}
            <p>
                <a href="https://teams.microsoft.com/l/meetup-join/19%3ameeting_YjliNGIxN2MtZjA0OC00MjgyLWJmYjQtMjYzMzIwMDZjNGZm%40thread.v2/0?context=%7b%22Tid%22%3a%228324ff4b-14c8-4bf5-b07e-a0713179f37e%22%2c%22Oid%22%3a%224d479202-a42c-46e9-b2f3-e8b1c1583ae9%22%7d"
                   target="_blank" rel="noopener noreferrer">
                    🎧 Pripojiť sa na MS Teams
                </a>
            </p>
            <p><a href="{{ route('question') }}">❓ Položiť otázku</a></p>
            <p><a href="{{ route('recordings.login') }}">📺 Príspevky z konferencie</a></p>
    {{--<div class="pill">{{ $deadline }}</div>--}}
    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e0e0e0;">
    <p><a href="{{ route('committee') }}">👥 Programový a organizačný výbor</a></p>
</div>
</header>

<div class="stack">
<section class="card infoCard">
    <h2>Rýchle info</h2>
    <img class="infoIllustration" src="{{ asset('images/bulb.png') }}" alt="Ilustrácia: žiarovka (nápady a inovácie)" loading="lazy"/>
    <p><b>Organizátor:</b> <a href="https://www.fri.uniza.sk" target="_blank">Fakulta riadenia a informatiky</a> Žilinská univerzita v Žiline</p>
    <p><b>Forma:</b> hybridná (prezenčne aj online)</p>
    <p><b>Adresa:</b> Fakulta riadenia a informatiky, Veľký diel 3323, 010 26 Žilina (<a href="https://maps.app.goo.gl/PaeifyvpnXGbSTbi6" target="_blank">Kde nás nájdete</a>)</p>
    <p><b>Dátum:</b> {{ $date }}</p>
    {{--<p><b>Deadline na registráciu:</b> {{ $deadline }}</p>--}}
    <p>
        <b>Online pripojenie (MS Teams):</b>
        <a href="https://teams.microsoft.com/l/meetup-join/19%3ameeting_YjliNGIxN2MtZjA0OC00MjgyLWJmYjQtMjYzMzIwMDZjNGZm%40thread.v2/0?context=%7b%22Tid%22%3a%228324ff4b-14c8-4bf5-b07e-a0713179f37e%22%2c%22Oid%22%3a%224d479202-a42c-46e9-b2f3-e8b1c1583ae9%22%7d"
           target="_blank" rel="noopener noreferrer">Otvoriť míting</a>
    </p>
    <p><b>Kontakt:</b> <a href="mailto:konferenciaAI@fri.uniza.sk">konferenciaAI@fri.uniza.sk</a></p>

</section>

<main class="card">
    <h2>Obsah</h2>

    <h3>Úvodný blok – Vízia a trendy</h3>
    <p><b>Cieľ:</b> predstaviť aktuálne smerovanie AI v oblasti vzdelávania a výskumu.</p>
    <p><b>Témy:</b></p>
    <ul>
        <li>Úloha umelej inteligencie v transformácii vysokého školstva</li>
        <li>Vzdelávanie pre budúcnosť: aké zručnosti musia mať absolventi v ére AI</li>
        <li>Etika, zodpovednosť a dôvera v AI vo vzdelávacom prostredí</li>
    </ul>

    <h3>Blok 1 – AI vo výučbe a výskume</h3>
    <p><b>Cieľ:</b> zdieľať skúsenosti univerzít a stredných škôl so zavádzaním AI do vzdelávania.</p>
    <p><b>Témy:</b></p>
    <ul>
        <li>AI asistenti vo výučbe (ChatGPT, Copilot, Claude a iné nástroje)</li>
        <li>Automatizované hodnotenie a spätná väzba pomocou AI</li>
        <li>AI pri tvorbe študijných materiálov a interaktívnych kurzov</li>
    </ul>

    <div class="block2">
        <h3>Blok 2 – AI v praxi</h3>
        <img class="blockIllustration" src="{{ asset('images/brain.png') }}" alt="Ilustrácia: mozog (umelá inteligencia)" loading="lazy"/>
        <p><b>Cieľ:</b> prepojiť univerzitné prostredie s potrebami firiem.</p>
        <p><b>Témy:</b></p>
        <ul>
            <li>Ako firmy používajú AI v praxi a čo očakávajú od absolventov</li>
            <li>Spolupráca univerzít a firiem pri vývoji AI riešení</li>
            <li>Etické a právne aspekty nasadzovania AI v praxi</li>
        </ul>
    </div>

    <h3>Blok 3 – AI očami študentov</h3>
    <p><b>Cieľ:</b> dať priestor študentom prezentovať svoje projekty a názory.</p>
    <p><b>Témy:</b></p>
    <ul>
        <li>Ako študenti používajú AI nástroje pri učení, príprave projektov alebo písaní záverečných prác</li>
        <li>Ako študenti vnímajú zodpovedné využívanie AI, plagiátorstvo a akademickú integritu</li>
        <li>Nápady na zlepšenie vzdelávania a podporu študentov prostredníctvom AI</li>
    </ul>

    <h3>Panelová diskusia</h3>
    <p>„Ako AI mení spôsob, akým (sa) učíme?“</p>
</main>
</div>
@endsection

