<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Prezenčná listina</title>
    <style>
        @page { margin: 16mm 12mm 18mm; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11.5px;
            color: #0f172a;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 6px 0;
        }

        .subtitle {
            margin: 0 0 10px 0;
            color: #334155;
            line-height: 1.35;
        }

        .subtitle strong {
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }

        thead th {
            background: #f1f5f9;
            color: #0f172a;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 8px;
            text-align: left;
            white-space: nowrap;
        }

        tbody td {
            padding: 9px 8px;
            border-top: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .num { width: 24px; text-align: right; color: #334155; }
        .name { width: 40%; }
        .inst { width: 32%; }
        .sign { width: 28%; }

        .muted { color: #475569; }
        .sign-space { height: 22px; }

        tr { page-break-inside: avoid; }
        thead { display: table-header-group; } /* repeat on every page */

        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>
    @php
        $listTitle = match (($participationTypeFilter ?? '')) {
            'active' => 'Zoznam prednášajúcich',
            'passive' => 'Zoznam účastníkov',
            default => 'Prezenčná listina',
        };
    @endphp

    <div class="footer">
        {{ $listTitle }} · Konferencia o umelej inteligencii vo vzdelávaní {{ $conference->year }}
    </div>

    <h1>{{ $listTitle }}</h1>
    <p class="subtitle">
        <strong>Konferencia o umelej inteligencii vo vzdelávaní</strong> · {{ $conference->editionLabel() }}<br>
        {{ \App\Models\Conference::formatDate($conference->date) ?? $conference->year }}, Fakulta riadenia a informatiky, Žilinská univerzita v Žiline
    </p>

    <table>
        <thead>
            <tr>
                <th class="num">#</th>
                <th class="name">Meno a priezvisko</th>
                <th class="inst">Inštitúcia</th>
                <th class="sign">Podpis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $i => $r)
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td class="name">
                        {{ $r->fullName() }}
                    </td>
                    <td class="inst">{{ $r->institution }}</td>
                    <td class="sign"><div class="sign-space"></div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($registrations->isEmpty())
        <p class="muted">Zvolenému filtru nezodpovedá žiadny účastník.</p>
    @endif
</body>
</html>

