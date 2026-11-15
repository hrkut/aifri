@extends('layouts.public')

@section('title', __('Program konferencie'))
@section('meta_description', __('Program konferencie o umelej inteligencii vo vzdelávaní'))

@section('content')

<style>
    .printOnly { display: none; }

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
        /* Print header: show logo div */
        .printHeader {
            display: block !important;
            margin-bottom: 5mm;
            page-break-inside: avoid;
            text-align: center !important;
        }

        .printHeaderLogo {
            max-width: 480px !important;
            height: auto;
            display: block;
            margin: 0 auto !important;
        }

        /* Hide screen header (logo, title, navigation) */
        .siteHeaderLink, .siteNav, header { display: none !important; }
        .printOnly { display: block !important; }

          /* Make text of talks black (keep break colors as-is) */
         .program-table tbody tr:not(.break-row) td {
             color: #000 !important;
         }

         .program-table tbody tr.details-row td,
         .program-table tbody tr.details-row .detail-label,
         .program-table tbody tr.details-row .detail-value {
             color: #000 !important;
         }

         /* Hide the bottom back button (not the header logo wrapper) */
         .back-home-link {
             display: none !important;
         }

         /* Hide MS Teams meeting button */
         a[href^="https://teams.microsoft.com/"] {
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

<x-site.hero :conference="$conference"
             :title="__('PROGRAM KONFERENCIE')"
             :sub="__('Umelá inteligencia vo vzdelávaní')" />

<div class="stack">
    <div class="card">
        <!-- Print-only header with FRI_logo2 -->
        <div class="printHeader" style="display: none; margin-bottom: 1.5rem; text-align: center;">
            <img src="{{ asset('images/FRI_logo2.png') }}" alt="{{ __('FRI Logo') }}" class="printHeaderLogo" style="width: 100%; max-width: 480px; height: auto;"/>
        </div>

        <h1 class="printOnly" style="color: #9fe7ff; margin-bottom: 1.5rem; margin-top: 0;">{{ __('Program konferencie') }}</h1>
        <p style="margin-top: -0.75rem; margin-bottom: 1.25rem; color: #cbd5e1; font-size: 0.95rem;">
            {{ __('Organizátori si vyhradzujú právo na zmeny v programe.') }}
        </p>

        @if($conference->teams_url)
        <div style="margin-bottom: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a
                href="{{ $conference->teams_url }}"
                target="_blank"
                rel="noopener noreferrer"
                style="display: inline-block; padding: 0.65rem 1rem; background: rgba(255, 184, 28, 0.16); color: #ffb81c; text-decoration: none; border-radius: 0.6rem; font-weight: 600; border: 1px solid rgba(255, 184, 28, 0.35);"
            >
                {{ __('Otvoriť MS Teams míting') }}
            </a>
        </div>
        @endif

        @if($registrations->isEmpty())
            <p style="color: #cbd5e1; text-align: center; padding: 2rem;">{{ __('Program zatiaľ nie je zverejnený.') }}</p>
        @else
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="program-table" style="width: 100%; min-width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: rgba(159, 231, 255, 0.1); border-bottom: 2px solid rgba(159, 231, 255, 0.3);">
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">{{ __('Čas') }}</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">{{ __('Trvanie') }}</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c; white-space: nowrap;">{{ __('Forma') }}</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">{{ __('Prednášajúci') }}</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">{{ __('Inštitúcia') }}</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #ffb81c;">{{ __('Názov príspevku') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $registration)
                            @php
                                $isBreak = $registration->participation_type === 'break';
                                $timeVal = $registration->time_start ? \Illuminate\Support\Carbon::parse($registration->time_start)->format('H:i') : '—';
                                $durVal = (int) ($registration->duration_minutes ?? 0);
                                $hasDetails = !$isBreak && ($registration->abstract || $registration->keywords);
                                $formLabel = $registration->online_participation ? __('Online') : __('Prezenčne');
                            @endphp

                            @if($isBreak)
                                <tr class="break-row" style="background: rgba(255, 166, 0, 0.18); border-top: 1px solid rgba(255, 166, 0, 0.40);">
                                    <td style="padding: 0.75rem; color: #ff9d00; font-weight: 500; font-size: 1rem;" data-label="{{ __('Čas') }}">{{ $timeVal }}</td>
                                    <td style="padding: 0.75rem; color: #ff9d00; font-weight: 500; font-size: 1rem;" data-label="{{ __('Trvanie') }}">{{ $durVal }} min</td>
                                    <td colspan="4" style="padding: 0.75rem; color: #ff9d00; font-weight: 500; font-size: 1rem;">
                                        {{ $registration->title ? __($registration->title) : __('Prestávka') }}
                                    </td>
                                </tr>
                            @else
                                <tr style="border-top: 1px solid rgba(159, 231, 255, 0.1);">
                                    <td style="padding: 0.75rem; color: #cbd5e1; font-weight: 500;" data-label="{{ __('Čas') }}">{{ $timeVal }}</td>
                                    <td style="padding: 0.75rem; color: #cbd5e1;" data-label="{{ __('Trvanie') }}">{{ $durVal }} min</td>
                                    <td style="padding: 0.75rem; color: #cbd5e1; white-space: nowrap;" data-label="{{ __('Forma') }}">{{ $formLabel }}</td>
                                    <td style="padding: 0.75rem; color: #f3f7fb;" data-label="{{ __('Prednášajúci') }}">
                                        {{ $registration->name }}
                                    </td>
                                    <td style="padding: 0.75rem; color: #cbd5e1;" data-label="{{ __('Inštitúcia') }}">{{ $registration->institution }}</td>
                                    <td style="padding: 0.75rem; color: #f3f7fb;" data-label="{{ __('Názov príspevku') }}" @if($hasDetails)class="program-title-cell" @endif>
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
                                        <div style="margin-top: 0.35rem;">
                                            <a href="{{ lroute('question') }}" style="color: #9fe7ff; text-decoration: underline; font-size: 0.85rem;">{{ __('Položiť otázku') }}</a>
                                        </div>
                                    </td>
                                </tr>
                                @if($hasDetails)
                                    <tr class="details-row" data-program-details="{{ $registration->id }}" hidden>
                                        <td colspan="6">
                                            <div class="program-details-content">
                                                @if($registration->abstract)
                                                    <span class="detail-label">{{ __('Abstrakt:') }}</span>
                                                    <div class="detail-value">{{ $registration->abstract }}</div>
                                                @endif
                                                @if($registration->keywords)
                                                    <span class="detail-label">{{ __('Kľúčové slová:') }}</span>
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

    </div>
</div>
@endsection

