@extends('layouts.public')

@section('title', __('Konferencia o umelej inteligencii vo vzdelávaní'))
@section('meta_description', __('Konferencia o umelej inteligencii vo vzdelávaní — AI in education'))

@php
    /** @var \App\Models\Conference $conference */
@endphp

@section('content')
    <x-site.hero :conference="$conference" :stamp="true"
                 :title="__('KONFERENCIA O UMELEJ INTELIGENCII<br>VO VZDELÁVANÍ')"
                 :sub="__('(Conference on AI in education)')" />

<div class="homeGrid">
    <section class="agenda" aria-labelledby="agenda-title">
        <h2 id="agenda-title">{{ __('Program dňa') }}</h2>
        <img class="agendaIllustration" src="{{ asset('images/brain.png') }}" alt="" loading="lazy"/>

        <ol class="agendaList">
            <li class="agendaItem">
                <h3>{{ __('Úvodný blok – Vízia a trendy') }}</h3>
                <p><b>{{ __('Cieľ:') }}</b> {{ __('predstaviť aktuálne smerovanie AI v oblasti vzdelávania a výskumu.') }}</p>
                <ul>
                    <li>{{ __('Úloha umelej inteligencie v transformácii vysokého školstva') }}</li>
                    <li>{{ __('Vzdelávanie pre budúcnosť: aké zručnosti musia mať absolventi v ére AI') }}</li>
                    <li>{{ __('Etika, zodpovednosť a dôvera v AI vo vzdelávacom prostredí') }}</li>
                </ul>
            </li>

            <li class="agendaItem">
                <h3>{{ __('Blok 1 – AI vo výučbe a výskume') }}</h3>
                <p><b>{{ __('Cieľ:') }}</b> {{ __('zdieľať skúsenosti univerzít a stredných škôl so zavádzaním AI do vzdelávania.') }}</p>
                <ul>
                    <li>{{ __('AI asistenti vo výučbe (ChatGPT, Copilot, Claude a iné nástroje)') }}</li>
                    <li>{{ __('Automatizované hodnotenie a spätná väzba pomocou AI') }}</li>
                    <li>{{ __('AI pri tvorbe študijných materiálov a interaktívnych kurzov') }}</li>
                </ul>
            </li>

            <li class="agendaItem">
                <h3>{{ __('Blok 2 – AI v praxi') }}</h3>
                <p><b>{{ __('Cieľ:') }}</b> {{ __('prepojiť univerzitné prostredie s potrebami firiem.') }}</p>
                <ul>
                    <li>{{ __('Ako firmy používajú AI v praxi a čo očakávajú od absolventov') }}</li>
                    <li>{{ __('Spolupráca univerzít a firiem pri vývoji AI riešení') }}</li>
                    <li>{{ __('Etické a právne aspekty nasadzovania AI v praxi') }}</li>
                </ul>
            </li>

            <li class="agendaItem agendaItem--panel">
                <h3>{{ __('Panelová diskusia') }}</h3>
                <blockquote>{{ __('„Ako AI mení spôsob, akým (sa) učíme?“') }}</blockquote>
            </li>
        </ol>
    </section>

    <x-site.info-card :conference="$conference" />
</div>
@endsection

