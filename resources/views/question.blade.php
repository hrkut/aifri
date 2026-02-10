@extends('layouts.public')

@section('title', 'Položiť otázku – Umelá inteligencia vo vzdelávaní')
@section('meta_description', 'Položte svoju otázku na konferenciu o umelej inteligencii vo vzdelávaní')

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
                        <h1>POLOŽIŤ OTÁZKU</h1>
                        <p class="sub">Umelá inteligencia vo vzdelávaní</p>
                    </div>
                </div>
            </div>

            <div class="meta">
                <p><b>Máte otázku?</b> Napíšte nám ju a my sa vám pokúsime odpovedať.</p>
            </div>
        </div>

        <div class="card loginCard" style="min-width:325px; max-width:360px;">
            <h2>Navigácia</h2>
            <p><a href="{{ route('home') }}">🏠 Späť na úvodnú stránku</a></p>
            <p><a href="{{ route('program') }}">📅 Program konferencie</a></p>
        </div>
    </header>

    <div class="stack">
        <section class="card lightCard">
            <h2>Položte svoju otázku</h2>

            @if ($errors->any())
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: #dc2626;">
                    <strong>Chyba pri odoslaní:</strong>
                    <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div id="question-success" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: #16a34a;">
                    Vaša otázka bola úspešne odoslaná. Ďakujeme vám za vašu otázku.
                </div>
                <script>
                    setTimeout(() => {
                        const el = document.getElementById('question-success');
                        if (el) el.style.display = 'none';
                    }, 5000);
                </script>
            @endif

            <form method="POST" action="{{ route('question.store') }}" style="max-width: 600px;">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label for="name" style="display: block; font-weight: 600; color: #9fe7ff; margin-bottom: 0.5rem;">
                        Meno <span style="color: #ef4444;">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="Vaše meno"
                        style="width: 100%; padding: 0.75rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 0.5rem; color: #f3f7fb; font-size: 1rem;"
                        required
                    />
                    @error('name')
                        <span style="color: #ef4444; font-size: 0.875rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>


                <div style="margin-bottom: 1.5rem;">
                    <label for="question" style="display: block; font-weight: 600; color: #9fe7ff; margin-bottom: 0.5rem;">
                        Otázka <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea
                        name="question"
                        id="question"
                        placeholder="Napíšte vašu otázku..."
                        rows="6"
                        style="width: 100%; padding: 0.75rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 0.5rem; color: #f3f7fb; font-size: 1rem; font-family: inherit; resize: vertical;"
                        required
                    ></textarea>
                    @error('question')
                        <span style="color: #ef4444; font-size: 0.875rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button type="submit" class="btn">
                        Odoslať otázku
                    </button>
                    <a href="{{ route('home') }}" class="btn secondary">
                        Zrušiť
                    </a>
                </div>
            </form>
        </section>
    </div>
@endsection

