<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Program konferencie {{ $conference->year }}</title>
    @php
        /** @var \App\Models\Conference $conference */
        /** @var \Illuminate\Support\Collection<int, \App\Models\Registration> $registrations */
        $talks = $registrations->where('participation_type', 'presentation')->count();
    @endphp
    <style>
        /* Print-friendly programme (dompdf): white paper, the site's petrol and amber as accents */
        @page { margin: 16mm 12mm 18mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #1f2a33;
        }

        .header { width: 100%; border-bottom: 2px solid #0b4b67; padding-bottom: 6pt; margin-bottom: 10pt; }
        .header td { vertical-align: middle; }
        .logo { height: 38pt; } /* FRI_logo_print.png: cropped, small copy of FRI_logo2.png */
        .titles { text-align: right; }
        h1 { margin: 0; font-size: 17pt; color: #0b4b67; letter-spacing: .3pt; }
        .subtitle { margin: 2pt 0 0; font-size: 9.5pt; color: #42525e; }

        .facts { margin: 0 0 8pt; font-size: 9pt; color: #42525e; }
        .facts b { color: #1f2a33; }
        .note { margin: 0 0 10pt; font-size: 8.5pt; color: #6b7a86; }

        table.program { width: 100%; border-collapse: collapse; }
        table.program thead { display: table-header-group; } /* repeat on every page */
        table.program th {
            background: #0b4b67;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            padding: 5pt 6pt;
        }
        table.program td {
            padding: 5pt 6pt;
            border-bottom: 0.6pt solid #d5dee4;
            vertical-align: top;
        }
        table.program tr { page-break-inside: avoid; }
        tr.alt td { background: #f4f8fa; }

        .time { width: 38pt; font-weight: bold; color: #0b4b67; white-space: nowrap; }
        .dur { width: 40pt; color: #6b7a86; white-space: nowrap; }
        .speaker { width: 135pt; }
        .inst { width: 120pt; color: #42525e; }
        .form { width: 52pt; color: #42525e; }
        .block { width: 64pt; color: #42525e; }
        .talk { font-weight: bold; color: #1f2a33; }

        /* breaks: light amber band with an amber edge */
        tr.break td { background: #fff4d6; color: #5c4400; font-style: italic; }
        tr.break td.time { border-left: 3pt solid #ffb81c; color: #5c4400; }

        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #6b7a86;
            border-top: 0.6pt solid #d5dee4;
            padding-top: 3pt;
        }
    </style>
</head>
<body>
    <div class="footer">
        Konferencia o umelej inteligencii vo vzdelávaní · {{ $conference->editionLabel() }} ·
        Fakulta riadenia a informatiky, Žilinská univerzita v Žiline · konferenciaAI@fri.uniza.sk
    </div>

    <table class="header">
        <tr>
            <td><img class="logo" src="{{ public_path('images/FRI_logo_print.png') }}" alt="Logo FRI"></td>
            <td class="titles">
                <h1>Program konferencie</h1>
                <p class="subtitle">Konferencia o umelej inteligencii vo vzdelávaní · {{ $conference->editionLabel() }}</p>
            </td>
        </tr>
    </table>

    <p class="facts">
        <b>Dátum konania:</b> {{ $conference->dateLabel() }} &nbsp;·&nbsp;
        <b>Miesto:</b> Fakulta riadenia a informatiky, Veľký diel 3323, Žilina (prezenčne aj online) &nbsp;·&nbsp;
        <b>Príspevky:</b> {{ $talks }}
    </p>
    <p class="note">Organizátori si vyhradzujú právo na zmeny v programe.</p>

    @if($registrations->isEmpty())
        <p>Program zatiaľ neobsahuje žiadne položky.</p>
    @else
        <table class="program">
            <thead>
                <tr>
                    <th class="time">Čas</th>
                    <th class="dur">Trvanie</th>
                    <th class="speaker">Prednášajúci</th>
                    <th class="inst">Inštitúcia</th>
                    <th>Názov príspevku</th>
                    <th class="form">Forma</th>
                    <th class="block">Blok</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $r)
                    @php
                        $time = $r->time_start ? \Illuminate\Support\Carbon::parse($r->time_start)->format('G:i') : '—';
                        $minutes = (int) ($r->duration_minutes ?? 0);
                        // zebra stripes count talks only, breaks have their own colour
                        $row = ($row ?? 0) + ($r->participation_type === 'break' ? 0 : 1);
                    @endphp

                    @if($r->participation_type === 'break')
                        <tr class="break">
                            <td class="time">{{ $time }}</td>
                            <td class="dur">{{ $minutes }} min</td>
                            <td colspan="5">{{ $r->title ?: 'Prestávka' }}</td>
                        </tr>
                    @else
                        <tr class="{{ $row % 2 === 0 ? 'alt' : '' }}">
                            <td class="time">{{ $time }}</td>
                            <td class="dur">{{ $minutes }} min</td>
                            <td class="speaker">{{ $r->fullName() ?: '—' }}</td>
                            <td class="inst">{{ $r->institution ?: '—' }}</td>
                            <td class="talk">{{ $r->title ?: '—' }}</td>
                            <td class="form">{{ $r->online_participation ? 'online' : 'prezenčne' }}</td>
                            <td class="block">{{ $r->block ? \Illuminate\Support\Str::before($r->blockLabel(), ' – ') : '—' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
