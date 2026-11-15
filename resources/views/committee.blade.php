@extends('layouts.public')
@section('title', __('Programový a organizačný výbor'))
@section('meta_description', __('Programový a organizačný výbor konferencie o umelej inteligencii vo vzdelávaní'))
@section('content')
    <x-site.hero :conference="$conference"
                 :title="__('PROGRAMOVÝ A ORGANIZAČNÝ VÝBOR')"
                 :sub="__('Konferencia o umelej inteligencii vo vzdelávaní')" />
    <div class="stack">
        <section class="card">
            <h2>{{ __('Programový a organizačný výbor') }}</h2>

            <p>{{ __('Konferencia sa koná pod záštitou dekana FRI') }} <strong>{{ __('prof. Ing. Emila Kršáka, PhD.') }}</strong></p>

            <div style="margin-top: 1.5rem;">
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">{{ __('doc. Ing. Michal Kvet, PhD.') }}</h4>
                    <p style="color: #A0A0A0; margin-bottom: 0.25rem;">{{ __('Predseda programového a organizačného výboru') }}</p>
                    <p style="color: #A0A0A0;">{{ __('Vedúci katedry informatiky, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline') }}</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">{{ __('doc. Ing. Patrik Hrkút, PhD.') }}</h4>
                    <p style="color: #A0A0A0; margin-bottom: 0.25rem;">{{ __('Člen programového a organizačného výboru') }}</p>
                    <p style="color: #A0A0A0;">{{ __('Vedúci katedry softvérových technológií, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline') }}</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">{{ __('doc. Ing. Viliam Lendel, PhD.') }}</h4>
                    <p style="color: #A0A0A0; margin-bottom: 0.25rem;">{{ __('Člen programového a organizačného výboru') }}</p>
                    <p style="color: #A0A0A0;">{{ __('Prodekan FRI pre vzdelávanie, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline') }}</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">{{ __('doc. Ing. Marek Kvet, PhD.') }}</h4>
                    <p style="color: #A0A0A0; margin-bottom: 0.25rem;">{{ __('Člen programového a organizačného výboru') }}</p>
                    <p style="color: #A0A0A0;">{{ __('Katedra informatiky, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline') }}</p>
                </div>
                <div class="committee-member" style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;">{{ __('Ing. Michal Ďuračík, PhD.') }}</h4>
                    <p style="color: #A0A0A0; margin-bottom: 0.25rem;">{{ __('Člen programového a organizačného výboru') }}</p>
                    <p style="color: #A0A0A0;">{{ __('Katedra softvérových technológií, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline') }}</p>
                </div>
            </div>
        </section>
        <x-site.info-card :conference="$conference" />
    </div>
@endsection
