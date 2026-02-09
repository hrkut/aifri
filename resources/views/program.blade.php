@extends('layouts.public')

@section('title', 'Program konferencie')
@section('meta_description', 'Program konferencie o umelej inteligencii vo vzdelávaní')

@section('content')
<a href="{{ route('home') }}" class="siteHeaderLink">
    <div class="siteHeader">
        <img src="{{ asset('images/FRI_logo.png') }}" alt="FRI Logo" class="friLogo" loading="lazy"/>
    </div>
</a>

<style>
    .program-details-content {
        padding: 1rem 0.75rem;
    }

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

    /* IMPORTANT: on desktop, hidden must actually hide the row */
    .details-row[hidden] {
        display: none;
    }

    .details-row {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.25s ease;
        background: rgba(159, 231, 255, 0.05);
        border-top: 1px solid rgba(159, 231, 255, 0.15);
    }

    .details-row.open {
        max-height: 800px;
        overflow: visible;
    }

    .details-row td {
        padding: 0 !important;
        border: none !important;
    }

    .program-toggle {
        all: unset;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        justify-content: space-between;
    }

    .program-toggle .program-title-text {
        flex: 1 1 auto;
        min-width: 0;
    }

    .title-icon {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 0.25rem;
        transition: transform 0.25s ease;
        flex: 0 0 18px;
        width: 18px;
        height: 18px;
        color: #cbd5e1;
    }

    .program-title-cell.expanded .title-icon {
        transform: rotate(180deg);
    }

    @media (max-width: 768px) {
        .program-table {
            display: block;
            border: none;
        }

        .program-table thead {
            display: none;
        }

        .program-table tbody {
            display: block;
        }

        .program-table tr {
            display: block;
            border: 1px solid rgba(159, 231, 255, 0.2);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            padding: 0;
            overflow: visible;
        }

        .program-table tr.break-row {
            background: rgba(255, 184, 28, 0.1) !important;
            border-color: rgba(255, 184, 28, 0.3);
        }

        .program-table td {
            display: block;
            padding: 0.5rem 0.75rem;
            border: none;
            border-bottom: 1px solid rgba(159, 231, 255, 0.1);
        }

        .program-table td:last-child {
            border-bottom: none;
        }

        .program-table tr.break-row td {
            border-bottom-color: rgba(255, 184, 28, 0.1);
        }

        .program-table td[data-label]::before {
            content: attr(data-label);
            font-weight: 600;
            color: #ffb81c;
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .program-table tr.break-row td[data-label]::before {
            color: #ffb81c;
        }

        .details-row {
            display: block;
            margin: 0.5rem 0.75rem 0.75rem;
            border-radius: 0.5rem;
            max-height: 0;
            overflow: hidden;
        }

        .details-row[hidden] {
            display: none;
        }

        .details-row.open {
            max-height: 1000px;
        }

        .details-row td {
            display: block;
            padding: 0 !important;
        }

        .details-row .program-details-content {
            padding: 0.75rem !important;
        }
    }

    @media print {
        /* Hide header/logo link */
        .siteHeaderLink,
        .siteHeader,
        .friLogo {
            display: none !important;
        }

        /* Hide back button if present in layout */
        a[href="{{ route('home') }}"] {
            display: none !important;
        }

        /* Hide toggle arrows/icons */
        .title-icon,
        .program-toggle svg,
        .program-toggle .title-icon {
            display: none !important;
        }

        /* Ensure details are expanded sensibly in print */
        .details-row[hidden] {
            display: none !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-program-toggle]')
            .forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();

                    const id = btn.getAttribute('data-program-toggle');
                    if (!id) return;

                    const detailsRow = document.querySelector(`[data-program-details="${id}"]`);
                    const cell = btn.closest('.program-title-cell');
                    if (!detailsRow || !cell) return;

                    const willOpen = detailsRow.hasAttribute('hidden');

                    if (willOpen) {
                        detailsRow.removeAttribute('hidden');
                        // allow layout before animating
                        requestAnimationFrame(() => detailsRow.classList.add('open'));
                        cell.classList.add('expanded');
                        btn.setAttribute('aria-expanded', 'true');
                    } else {
                        detailsRow.classList.remove('open');
                        cell.classList.remove('expanded');
                        btn.setAttribute('aria-expanded', 'false');
                        // wait for animation then hide
                        setTimeout(() => detailsRow.setAttribute('hidden', ''), 220);
                    }
                });
            });
    });
</script>

<div class="stack" style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
    <div class="card" style="background: rgba(11, 75, 103, 0.35); padding: 2rem;">
        <h1 style="color: #9fe7ff; margin-bottom: 1.5rem; margin-top: 0;">Program konferencie</h1>
        <p style="margin-top: -0.75rem; margin-bottom: 1.25rem; color: #cbd5e1; font-size: 0.95rem;">
            Organizátori si vyhradzujú právo zmeny programu.
        </p>

        <div style="margin-bottom: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a
                href="https://teams.microsoft.com/l/meetup-join/19%3ameeting_YjliNGIxN2MtZjA0OC00MjgyLWJmYjQtMjYzMzIwMDZjNGZm%40thread.v2/0?context=%7b%22Tid%22%3a%228324ff4b-14c8-4bf5-b07e-a0713179f37e%22%2c%22Oid%22%3a%224d479202-a42c-46e9-b2f3-e8b1c1583ae9%22%7d"
                target="_blank"
                rel="noopener noreferrer"
                style="display: inline-block; padding: 0.65rem 1rem; background: rgba(255, 184, 28, 0.16); color: #ffb81c; text-decoration: none; border-radius: 0.6rem; font-weight: 600; border: 1px solid rgba(255, 184, 28, 0.35);"
            >
                Otvoriť MS Teams míting
            </a>
        </div>

        @if($registrations->isEmpty())
            <p style="color: #cbd5e1; text-align: center; padding: 2rem;">Program zatiaľ nie je zverejnený.</p>
        @else
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="program-table" style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: rgba(159, 231, 255, 0.1); border-bottom: 2px solid rgba(159, 231, 255, 0.3);">
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">Čas</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">Trvanie</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">Prednášajúci</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">Inštitúcia</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">Názov príspevku</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $registration)
                            @php
                                $isBreak = $registration->participation_type === 'break';
                                $timeVal = $registration->time_start ? \Illuminate\Support\Carbon::parse($registration->time_start)->format('H:i') : '—';
                                $durVal = (int) ($registration->duration_minutes ?? 0);
                                $hasDetails = !$isBreak && ($registration->abstract || $registration->keywords);
                            @endphp

                            @if($isBreak)
                                <tr class="break-row" style="background: rgba(255, 184, 28, 0.1); border-top: 1px solid rgba(255, 184, 28, 0.2);">
                                    <td style="padding: 0.75rem; color: #ffb81c; font-weight: 500;" data-label="Čas">{{ $timeVal }}</td>
                                    <td style="padding: 0.75rem; color: #ffb81c;" data-label="Trvanie">{{ $durVal }} min</td>
                                    <td colspan="3" style="padding: 0.75rem; color: #ffb81c; font-weight: 600;">
                                        {{ $registration->title ?? 'Prestávka' }}
                                    </td>
                                </tr>
                            @else
                                <tr style="border-top: 1px solid rgba(159, 231, 255, 0.1);">
                                    <td style="padding: 0.75rem; color: #cbd5e1; font-weight: 500;" data-label="Čas">{{ $timeVal }}</td>
                                    <td style="padding: 0.75rem; color: #cbd5e1;" data-label="Trvanie">{{ $durVal }} min</td>
                                    <td style="padding: 0.75rem; color: #f3f7fb;" data-label="Prednášajúci">
                                        {{ $registration->name }}
                                    </td>
                                    <td style="padding: 0.75rem; color: #cbd5e1;" data-label="Inštitúcia">{{ $registration->institution }}</td>
                                    <td style="padding: 0.75rem; color: #f3f7fb;" data-label="Názov príspevku" @if($hasDetails)class="program-title-cell" @endif>
                                        @if($hasDetails)
                                            <button type="button" class="program-toggle" data-program-toggle="{{ $registration->id }}" aria-expanded="false">
                                                <span class="program-title-text">{{ $registration->title ?? '—' }}</span>
                                                <span class="title-icon" aria-hidden="true">
                                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                        <path d="M5 7.5l5 5 5-5" />
                                                    </svg>
                                                </span>
                                            </button>
                                        @else
                                            {{ $registration->title ?? '—' }}
                                        @endif
                                    </td>
                                </tr>
                                @if($hasDetails)
                                    <tr class="details-row" data-program-details="{{ $registration->id }}" hidden>
                                        <td colspan="5">
                                            <div class="program-details-content">
                                                @if($registration->abstract)
                                                    <span class="detail-label">Abstrakt:</span>
                                                    <div class="detail-value">{{ $registration->abstract }}</div>
                                                @endif
                                                @if($registration->keywords)
                                                    <span class="detail-label">Kľúčové slová:</span>
                                                    <div class="detail-value">{{ $registration->keywords }}</div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endif
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

