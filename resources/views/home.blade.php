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

<div class="homeGrid">
    <section class="agenda" aria-labelledby="agenda-title">
        <h2 id="agenda-title">Program dňa</h2>
        <img class="agendaIllustration" src="{{ asset('images/brain.png') }}" alt="" loading="lazy"/>

        <ol class="agendaList">
            <li class="agendaItem">
                <h3>Úvodný blok – Vízia a trendy</h3>
                <p><b>Cieľ:</b> predstaviť aktuálne smerovanie AI v oblasti vzdelávania a výskumu.</p>
                <ul>
                    <li>Úloha umelej inteligencie v transformácii vysokého školstva</li>
                    <li>Vzdelávanie pre budúcnosť: aké zručnosti musia mať absolventi v ére AI</li>
                    <li>Etika, zodpovednosť a dôvera v AI vo vzdelávacom prostredí</li>
                </ul>
            </li>

            <li class="agendaItem">
                <h3>Blok 1 – AI vo výučbe a výskume</h3>
                <p><b>Cieľ:</b> zdieľať skúsenosti univerzít a stredných škôl so zavádzaním AI do vzdelávania.</p>
                <ul>
                    <li>AI asistenti vo výučbe (ChatGPT, Copilot, Claude a iné nástroje)</li>
                    <li>Automatizované hodnotenie a spätná väzba pomocou AI</li>
                    <li>AI pri tvorbe študijných materiálov a interaktívnych kurzov</li>
                </ul>
            </li>

            <li class="agendaItem">
                <h3>Blok 2 – AI v praxi</h3>
                <p><b>Cieľ:</b> prepojiť univerzitné prostredie s potrebami firiem.</p>
                <ul>
                    <li>Ako firmy používajú AI v praxi a čo očakávajú od absolventov</li>
                    <li>Spolupráca univerzít a firiem pri vývoji AI riešení</li>
                    <li>Etické a právne aspekty nasadzovania AI v praxi</li>
                </ul>
            </li>

            <li class="agendaItem agendaItem--panel">
                <h3>Panelová diskusia</h3>
                <blockquote>„Ako AI mení spôsob, akým (sa) učíme?“</blockquote>
            </li>
        </ol>
    </section>

    <x-site.info-card :conference="$conference" />
</div>
@endsection

