@extends('layouts.public')

@section('title', 'Príspevky z konferencie')
@section('meta_description', 'Záznamy z konferencie o umelej inteligencii vo vzdelávaní')

@section('content')
<a href="{{ route('home') }}" class="siteHeaderLink">
    <div class="siteHeader">
        <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
    </div>
</a>

<style>
    .detail-label {
        font-weight: 600;
        color: #ffb81c;
        font-size: 0.85rem;
        margin-top: 0.75rem;
        margin-bottom: 0.25rem;
        display: block;
    }

    .detail-value {
        color: #cbd5e1;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .recordings-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }

    .recordings-table thead tr {
        background: rgba(159, 231, 255, 0.1);
        border-bottom: 2px solid rgba(159, 231, 255, 0.3);
    }

    .recordings-table th {
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        color: #ffb81c;
    }

    .recordings-table tbody tr {
        border-top: 1px solid rgba(159, 231, 255, 0.1);
    }

    .recordings-table td {
        padding: 0.75rem;
        color: #f3f7fb;
    }

    .recordings-table td[data-label] {
        color: #cbd5e1;
    }

    .recordings-table td[data-label="Prednášajúci"],
    .recordings-table td[data-label="Názov príspevku"] {
        color: #f3f7fb;
    }

    .recordings-icons-cell {
        display: inline-flex;
        gap: 0;
        align-items: center;
        justify-content: center;
    }

    .icon-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 -0.1rem;
        background: none;
        color: #00e5cc;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        padding: 0.25rem 0.25rem;
        border-radius: 4px;
        position: relative;
        line-height: 1;
        white-space: nowrap;
    }

    .icon-link:hover {
        background: rgba(0, 229, 204, 0.15);
        transform: scale(1.15);
    }

    .icon-link.disabled {
        opacity: 0.25;
        cursor: not-allowed;
        color: #cbd5e1;
    }

    .icon-link.disabled:hover {
        background: none;
        transform: none;
    }

    .recording-icon::before {
        content: '🎬';
        font-size: 1.1rem;
    }

    .presentation-icon::before {
        content: '📑';
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .recordings-table {
            font-size: 0.85rem;
        }

        .recordings-table thead {
            display: none;
        }

        .recordings-table tbody {
            display: block;
        }

        .recordings-table tr {
            display: block;
            border: 1px solid rgba(159, 231, 255, 0.2);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            padding: 0;
            overflow: visible;
        }

        .recordings-table td {
            display: block;
            padding: 0.5rem 0.75rem;
            border: none;
            border-bottom: 1px solid rgba(159, 231, 255, 0.1);
        }

        .recordings-table td:last-child {
            border-bottom: none;
        }

        .recordings-table td[data-label]::before {
            content: attr(data-label);
            font-weight: 600;
            color: #ffb81c;
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .recordings-icons-cell {
            display: inline-flex !important;
            gap: 0;
            align-items: center;
            justify-content: center;
        }
    }
</style>

<div class="stack" style="max-width: 90vw; margin: 2rem auto; padding: 0 1rem;">
    <div class="card" style="background: rgba(11, 75, 103, 0.35); padding: 2rem;">
        <h1 style="color: #9fe7ff; margin-bottom: 1.5rem; margin-top: 0;">Príspevky z konferencie</h1>
        <p style="margin-top: -0.75rem; margin-bottom: 1.25rem; color: #cbd5e1; font-size: 0.95rem;">
            Konferencia o AI vo vzdelávaní – 11.2.2026
        </p>

        @if($registrations->isEmpty())
            <p style="color: #cbd5e1; text-align: center; padding: 2rem;">Zatiaľ nie sú k dispozícii žiadne záznamy.</p>
        @else
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="recordings-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Čas</th>
                            <th style="min-width: 200px;">Prednášajúci</th>
                            <th style="width: 150px;">Inštitúcia</th>
                            <th style="flex: 1; min-width: 250px;">Názov príspevku</th>
                            <th style="width: 100px; text-align: center;">Záznamy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $registration)
                            @php
                                $timeVal = $registration->time_start ? \Illuminate\Support\Carbon::parse($registration->time_start)->format('H:i') : '—';
                                $durVal = (int) ($registration->duration_minutes ?? 0);
                            @endphp
                            <tr>
                                <td style="font-weight: 500;" data-label="Čas">{{ $timeVal }}</td>
                                <td data-label="Prednášajúci">{{ $registration->name }}</td>
                                <td data-label="Inštitúcia">{{ $registration->institution ?? '—' }}</td>
                                <td data-label="Názov príspevku">{{ $registration->title ?? '(bez názvu)' }}</td>
                                <td style="text-align: center;" data-label="Záznamy">
                                    <div class="recordings-icons-cell">
                                        @if($registration->record)
                                            <a href="{{ route('recordings.download', $registration->record) }}"
                                               class="icon-link recording-icon"
                                               title="Stiahnuť video záznam">
                                            </a>
                                        @else
                                            <span class="icon-link recording-icon disabled" title="Video záznam nie je k dispozícii"></span>
                                        @endif

                                        @if($registration->presentation)
                                            <a href="{{ route('recordings.presentation', $registration->presentation) }}"
                                               class="icon-link presentation-icon"
                                               title="Stiahnuť prezentáciu">
                                            </a>
                                        @else
                                            <span class="icon-link presentation-icon disabled" title="Prezentácia nie je k dispozícii"></span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div style="margin-top: 2rem; text-align: center;">
            <a href="{{ route('home') }}" style="display: inline-block; padding: 0.75rem 1.5rem; background: rgba(159, 231, 255, 0.2); color: #00e5cc; text-decoration: none; border-radius: 0.5rem; font-weight: 500; border: 1px solid rgba(159, 231, 255, 0.3); transition: all 0.2s ease;">
                ← Späť na hlavnú stránku
            </a>
        </div>
    </div>
</div>
@endsection

