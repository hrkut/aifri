@extends('layouts.public')

@section('title', 'Prihlásenie na konferenciu – Umelá inteligencia vo vzdelávaní')
@section('meta_description', 'Prihláste sa na konferenciu o umelej inteligencii vo vzdelávaní')

@php
    $place = 'Fakulta riadenia a informatiky, Žilinská univerzita v Žiline';
    $date = '11. 2. 2026 (streda) od 9:00';
    $deadline = 'Už len online do 9. 2. 2026';
@endphp

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
                        <h1>PRIHLÁSENIE NA KONFERENCIU</h1>
                        <p class="sub">Umelá inteligencia vo vzdelávaní</p>
                    </div>
                </div>
            </div>

            <div class="meta">
                <p><b>Organizátor:</b> <a href="https://www.fri.uniza.sk" target="_blank">Fakulta riadenia a informatiky</a> Žilinská univerzita v Žiline</p>
                <p><b>Forma:</b> hybridná (prezenčne aj online)</p>
                <p><b>Adresa:</b> Fakulta riadenia a informatiky, Veľký diel 3323, 010 26 Žilina (<a href="https://maps.app.goo.gl/PaeifyvpnXGbSTbi6" target="_blank">Kde nás nájdete</a>)</p>
                <p><b>Dátum:</b> {{ $date }}</p>
                <p><b>Deadline:</b> {{ $deadline }}</p>
            </div>
        </div>

        <div class="card loginCard" style="min-width:325px; max-width:360px;">
            <h2>Navigácia</h2>
            <p><a href="{{ route('home') }}">🏠 Späť na úvodnú stránku</a></p>
            <p><a href="{{ route('committee') }}">👥 Programový a organizačný výbor</a></p>
        </div>
    </header>

    <div class="stack">
        <section class="card lightCard">
            <h2>Typy účasti</h2>
            <p><b>Účasť je možná už len online a prihlasovanie príspevkov je uzatvorené.</b></p>

            <div class="registrationTypes">
                <div class="regType">
                    <h3>Pasívna účasť (online)</h3>
                    <p>Pre účastníkov, ktorí sa chcú zúčastniť ako poslucháči.</p>
                    <ul class="list">
                        <li>Online prístup na všetky prednášky</li>
                        <li>Účasť na panelových diskusiách</li>
                    </ul>
                </div>
            </div>
        </section>

        <main class="card lightCard">
            <h2 id="registration-form">Registračný formulár</h2>

            @if(session('success'))
                <div class="success-message">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="error-box">
                    <strong>Opravte prosím nasledujúce chyby:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="regForm" method="POST" action="{{ route('registration.submit') }}" novalidate>
                @csrf

                <div class="formSection">
                    <h3>Základné údaje</h3>

                    <div class="formGroup">
                        <label for="title_before">Titul pred menom</label>
                        <input type="text" id="title_before" name="title_before" value="{{ old('title_before') }}" placeholder="napr. Dr., Prof., Ing." class="@error('title_before') has-error @enderror">
                        @error('title_before')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="name">Meno a priezvisko *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="@error('name') has-error @enderror">
                        @error('name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="title_after">Titul za menom</label>
                        <input type="text" id="title_after" name="title_after" value="{{ old('title_after') }}" placeholder="napr. PhD., MBA" class="@error('title_after') has-error @enderror">
                        @error('title_after')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"  class="@error('email') has-error @enderror">
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="phone">Telefón</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="@error('phone') has-error @enderror">
                        @error('phone')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="institution">Inštitúcia / Organizácia *</label>
                        <input type="text" id="institution" name="institution" value="{{ old('institution') }}"  class="@error('institution') has-error @enderror">
                        @error('institution')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="position">Pozícia / Študijný program</label>
                        <input type="text" id="position" name="position" value="{{ old('position') }}" class="@error('position') has-error @enderror">
                        @error('position')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="formSection">
                    <h3>Forma a typ účasti</h3>
                    <div class="formGroup">
                        <div style="color: rgba(255,255,255,.85); font-size: 0.98em;">
                            Účasť je <b>len online</b> a <b>len pasívna</b>.
                        </div>
                    </div>
                </div>

                <div class="formSection contributionSection" style="display:none;">
                    <h3>Informácie o príspevku</h3>

                    <div class="formGroup">
                        <label for="title">Názov príspevku</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" class="@error('title') has-error @enderror">
                        @error('title')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="abstract">Abstrakt (max 300 slov)</label>
                        <textarea id="abstract" name="abstract" rows="6" class="@error('abstract') has-error @enderror">{{ old('abstract') }}</textarea>
                        @error('abstract')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="keywords">Kľúčové slová (oddelené čiarkou)</label>
                        <input type="text" id="keywords" name="keywords" value="{{ old('keywords') }}" class="@error('keywords') has-error @enderror">
                        @error('keywords')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="block">Preferovaný blok</label>
                        <select id="block" name="block" class="@error('block') has-error @enderror">
                            <option value="">-- Vyberte blok --</option>
                            <option value="intro" {{ old('block') == 'intro' ? 'selected' : '' }}>Úvodný blok – Vízia a trendy</option>
                            <option value="teaching" {{ old('block') == 'teaching' ? 'selected' : '' }}>Blok 1 – AI vo výučbe a výskume</option>
                            <option value="practice" {{ old('block') == 'practice' ? 'selected' : '' }}>Blok 2 – AI v praxi</option>
                            <option value="students" {{ old('block') == 'students' ? 'selected' : '' }}>Blok 3 – AI očami študentov</option>
                        </select>
                        @error('block')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="formSection">
                    <h3>Doplňujúce informácie</h3>

                    <div class="formGroup">
                        <label for="notes">Poznámky</label>
                        <textarea id="notes" name="notes" rows="3" class="@error('notes') has-error @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="formActions">
                    <a href="{{ route('home') }}" class="btn secondary">Späť na hlavnú stránku</a>
                    <button type="submit" class="btn">Odoslať prihlášku</button>
                </div>
            </form>
        </main>

        <aside class="card lightCard">
            <h2>Dôležité informácie</h2>
            <p><b>Deadline:</b> {{ $deadline }}</p>
            <p><b>Potvrdenie účasti:</b> Do 7 dní od prihlásenia</p>
            <p><b>Registračný poplatok:</b> Konferencia je bezplatná</p>
            <p><b>Kontakt:</b> <a href="mailto:konferenciaAI@fri.uniza.sk">konferenciaAI@fri.uniza.sk</a></p>

            <h3 style="margin-top:18px;">Dôležité termíny</h3>
            <ul class="list">
                <li>{{ $deadline }} – Deadline na prihlášky</li>
                <li>7. 2. 2026 – Notifikácia autorov</li>
                <li>{{ $date }} – Konferencia</li>
            </ul>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // If the page was redirected back with a success flash, scroll the registration form into view.
            @if(session('success'))
                const heading = document.getElementById('registration-form');
                if (heading) {
                    heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            @endif

            // Form validation
            const form = document.querySelector('.regForm');

            // Remove error when user starts typing
            form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('input', function() {
                    removeError(this);
                });
            });

            form.addEventListener('submit', function(e) {
                // Clear previous errors
                document.querySelectorAll('.error-message').forEach(msg => msg.remove());
                document.querySelectorAll('.has-error').forEach(field => field.classList.remove('has-error'));

                let hasErrors = false;

                // Validate required fields
                const requiredFields = [
                    { id: 'name', label: 'Meno a priezvisko' },
                    { id: 'email', label: 'E-mail' },
                    { id: 'institution', label: 'Inštitúcia / Organizácia' }
                ];

                requiredFields.forEach(field => {
                    const input = document.getElementById(field.id);
                    if (!input.value.trim()) {
                        showError(input, `${field.label} je povinné pole`);
                        hasErrors = true;
                    }
                });

                // Validate email format
                const emailField = document.getElementById('email');
                if (emailField.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailField.value)) {
                        showError(emailField, 'Zadajte platnú e-mailovú adresu');
                        hasErrors = true;
                    }
                }

                if (hasErrors) {
                    e.preventDefault();
                    const firstError = document.querySelector('.has-error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            function showError(field, message) {
                field.classList.add('has-error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                field.parentElement.appendChild(errorDiv);
            }

            function removeError(field) {
                field.classList.remove('has-error');
                const errorMessage = field.parentElement.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.remove();
                }
            }
        });
    </script>
@endsection

