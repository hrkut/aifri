@extends('layouts.public')

@section('title', 'Prihlásenie na konferenciu – Umelá inteligencia vo vzdelávaní')
@section('meta_description', 'Prihláste sa na konferenciu o umelej inteligencii vo vzdelávaní')

@php
    /** @var \App\Models\Conference $conference */
    $date = $conference->dateLabel();
    $deadline = \App\Models\Conference::formatDate($conference->registration_deadline);
    // Shown separately only when the edition has its own deadline for contributions
    $contributionDeadline = $conference->contribution_deadline ? \App\Models\Conference::formatDate($conference->contributionDeadline()) : null;
    $contributionOpen = $conference->isContributionOpen();
    $notification = \App\Models\Conference::formatDate($conference->notification_date);
@endphp

@section('content')
    <x-site.hero :conference="$conference"
                 title="PRIHLÁSENIE NA KONFERENCIU"
                 sub="Umelá inteligencia vo vzdelávaní" />

    <div class="stack">
        <x-site.info-card :conference="$conference" illustration="computer.png" illustrationAlt="Ilustrácia: počítač">
            <p><b>Potvrdenie účasti:</b> e-mailom ihneď po prihlásení</p>
            <p><b>Registračný poplatok:</b> konferencia je bezplatná</p>

            <h3>Dôležité termíny</h3>
            <ul class="list">
                @if($contributionDeadline)
                <li>{{ $contributionDeadline }} – Deadline na príspevky (aktívna účasť)</li>
                @endif
                @if($deadline)
                <li>{{ $deadline }} – {{ $contributionDeadline ? 'Deadline na registráciu (pasívna účasť)' : 'Deadline na prihlášky' }}</li>
                @endif
                @if($notification)
                <li>{{ $notification }} – Notifikácia autorov</li>
                @endif
                <li>{{ $date }} – Konferencia</li>
            </ul>
        </x-site.info-card>

        <section class="card">
            <h2>Typy účasti</h2>
            <p>Vyberte si formu vašej účasti na konferencii:</p>

            <div class="registrationTypes">
                <div class="regType">
                    <h3>Aktívna účasť s príspevkom</h3>
                    <p>Pre autorov, ktorí chcú prezentovať svoje skúsenosti.</p>
                    <ul class="list">
                        <li>Príspevok (max. {{ $conference->presentation_minutes ?: 15 }} minút)</li>
                        <li>Diskutujúci na panelovej diskusii (v prípade záujmu)</li>
                    </ul>
                    @if(! $contributionOpen)
                        <p><b>Prihlasovanie príspevkov je uzatvorené.</b></p>
                    @elseif($contributionDeadline)
                        <p><b>Príspevky prijímame do {{ $contributionDeadline }}.</b></p>
                    @endif
                </div>

                <div class="regType">
                    <h3>Pasívna účasť</h3>
                    <p>Pre účastníkov, ktorí sa chcú zúčastniť ako poslucháči.</p>
                    <ul class="list">
                        <li>Prístup na všetky prednášky (prezenčne aj online)</li>
                        <li>Účasť na panelových diskusiách</li>
                    </ul>
                </div>
            </div>
        </section>

        <main class="card">
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
                    <h3>Typ účasti *</h3>

                    <div class="formGroup radioGroup">
                        <label @unless($contributionOpen) style="opacity: .55;" @endunless>
                            <input type="radio" name="participation_type" value="presentation" {{ $contributionOpen && old('participation_type', 'passive') === 'presentation' ? 'checked' : '' }} @disabled(! $contributionOpen)>
                            Aktívna účasť – prednáška
                            @unless($contributionOpen)
                                (prihlasovanie príspevkov je uzatvorené)
                            @endunless
                        </label>
                        <label>
                            <input type="radio" name="participation_type" value="passive" {{ old('participation_type', 'passive') === 'passive' ? 'checked' : '' }}>
                            Pasívna účasť (poslucháč)
                        </label>
                    </div>
                    @error('participation_type')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="formSection">
                    <h3>Forma účasti</h3>

                    <div class="formGroup radioGroup">
                        <label>
                            <input type="checkbox" name="online_participation" value="1" {{ old('online_participation') ? 'checked' : '' }}>
                            Online účasť
                        </label>
                        <div style="margin-top:6px; color: rgba(255,255,255,.75); font-size: 0.95em;">
                            Ak zaškrtnete, budeme s vami počítať ako s online účastníkom. Inak počítame s prezenčnou účasťou v Žiline.
                        </div>
                    </div>
                </div>

                <div class="formSection contributionSection">
                    <h3>Informácie o príspevku</h3>

                    <div class="formGroup">
                        <label for="title">Názov príspevku *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" class="@error('title') has-error @enderror">
                        @error('title')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="abstract">Abstrakt (max 300 slov) *</label>
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
                            @foreach(\App\Models\Registration::BLOCKS as $key => $label)
                                <option value="{{ $key }}" @selected(old('block') === $key)>{{ $label }}</option>
                            @endforeach
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

            // Show the contribution fields only for active participation
            const contributionSection = document.querySelector('.contributionSection');
            function updateContributionVisibility() {
                const selected = document.querySelector('input[name="participation_type"]:checked');
                contributionSection.style.display = selected && selected.value === 'presentation' ? '' : 'none';
            }
            updateContributionVisibility();
            document.querySelectorAll('input[name="participation_type"]').forEach(radio => {
                radio.addEventListener('change', updateContributionVisibility);
            });

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

                // Contribution fields are required for active participation
                const participation = document.querySelector('input[name="participation_type"]:checked');
                if (participation && participation.value === 'presentation') {
                    const titleField = document.getElementById('title');
                    const abstractField = document.getElementById('abstract');

                    if (!titleField.value.trim()) {
                        showError(titleField, 'Názov príspevku je povinný');
                        hasErrors = true;
                    }

                    if (!abstractField.value.trim()) {
                        showError(abstractField, 'Abstrakt je povinný');
                        hasErrors = true;
                    } else {
                        const wordCount = abstractField.value.trim().split(/\s+/).length;
                        if (wordCount > {{ \App\Models\Registration::ABSTRACT_MAX_WORDS }}) {
                            showError(abstractField, `Abstrakt prekračuje limit {{ \App\Models\Registration::ABSTRACT_MAX_WORDS }} slov (aktuálne: ${wordCount} slov)`);
                            hasErrors = true;
                        }
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

