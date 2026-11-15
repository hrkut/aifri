@extends('layouts.public')

@section('title', 'Konferencia o umelej inteligencii vo vzdelávaní')
@section('meta_description', 'Konferencia o umelej inteligencii vo vzdelávaní — AI in education')

@php
    /** @var \App\Models\Conference $conference */
@endphp

@section('content')
    <x-site.hero :conference="$conference" :stamp="true"
                 title="KONFERENCIA O UMELEJ INTELIGENCII<br>VO VZDELÁVANÍ"
                 sub="(Conference on AI in education)" />

<div class="stack">
<x-site.info-card :conference="$conference" />

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

