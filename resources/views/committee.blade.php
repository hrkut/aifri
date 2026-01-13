@extends('layouts.public')
@section('title', 'Programový a organizačný výbor')
@section('meta_description', 'Programový a organizačný výbor konferencie o umelej inteligencii vo vzdelávaní')
@section('content')
    <a href="{{ route('home') }}" class="siteHeaderLink">
        <div class="siteHeader">
            <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
        </div>
    </a>
    <header>
        <div>
            <div class="heroTitle">
                <div class="heroText">
                    <div>
                        <h1>PROGRAMOVÝ A ORGANIZAČNÝ VÝBOR</h1>
                        <p class="sub">Konferencia o umelej inteligencii vo vzdelávaní</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card loginCard" style="min-width:325px; max-width:360px;">
            <h2>Navigácia</h2>
            <p><a href="{{ route('home') }}">Späť na úvodnú stránku</a></p>
            <p><a href="{{ route('registration') }}">Registrácia</a></p>
        </div>
    </header>
    <div class="stack">
        <section class="card">
            <h2>Programový a organizačný výbor</h2>

            <p>Konferencia sa koná pod záštitou dekana FRI <strong>prof. Ing. Emila Kršáka, PhD.</strong></p>

            <div style="margin-top: 1.5rem;">
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">doc. Ing. Michal Kvet, PhD.</h4>
                    <p style="color: #666; margin-bottom: 0.25rem;">Predseda programového a organizačného výboru</p>
                    <p style="color: #666;">Vedúci katedry informatiky, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">doc. Ing. Patrik Hrkút, PhD.</h4>
                    <p style="color: #666; margin-bottom: 0.25rem;">Člen programového a organizačného výboru</p>
                    <p style="color: #666;">Vedúci katedry softvérových technológií, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">doc. Ing. Viliam Lendel, PhD.</h4>
                    <p style="color: #666; margin-bottom: 0.25rem;">Člen programového a organizačného výboru</p>
                    <p style="color: #666;">Prodekan FRI pre vzdelávanie, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">doc. Ing. Marek Kvet, PhD.</h4>
                    <p style="color: #666; margin-bottom: 0.25rem;">Člen programového a organizačného výboru</p>
                    <p style="color: #666;">Katedra informatiky, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">Ing. Michal Ďuračík, PhD.</h4>
                    <p style="color: #666; margin-bottom: 0.25rem;">Člen programového a organizačného výboru</p>
                    <p style="color: #666;">Katedra softvérových technológií, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline</p>
                </div>
            </div>
        </section>
        <section class="card infoCard">
            <h2>Kontakt</h2>
            <p><b>E-mail:</b> <a href="mailto:konferenciaAI@fri.uniza.sk">konferenciaAI@fri.uniza.sk</a></p>
            <p><b>Web:</b> <a href="{{ route('home') }}">konferenciaAI.fri.uniza.sk</a></p>
            <p><b>Adresa:</b> Fakulta riadenia a informatiky, Univerzitná 8215/1, 010 26 Žilina</p>
        </section>
    </div>
@endsection
