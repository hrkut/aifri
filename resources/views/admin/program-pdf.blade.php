<!doctype html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Program konferencie</title>
    <style>
        /* wkhtmltopdf-friendly styles – inspired by /program */
        @page { margin: 18mm 12mm; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11.5px;
            color: #e2e8f0;
            background: #0b1220;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 8px 0;
            color: #f3f7fb;
            letter-spacing: 0.2px;
        }

        .note {
            font-size: 10.5px;
            color: #cbd5e1;
            margin: 0 0 14px 0;
        }

        .accent { color: #ffb81c; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(159, 231, 255, 0.18);
        }

        thead th {
            background: rgba(159, 231, 255, 0.08);
            color: #f3f7fb;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.6px;
            border-bottom: 1px solid rgba(159, 231, 255, 0.18);
            padding: 8px 8px;
            text-align: left;
        }

        tbody td {
            padding: 8px 8px;
            border-top: 1px solid rgba(159, 231, 255, 0.12);
            vertical-align: top;
            color: #e2e8f0;
        }

        .row-alt td {
            background: rgba(159, 231, 255, 0.04);
        }

        .break td {
            background: rgba(255, 184, 28, 0.10);
            font-weight: 700;
            color: #fef3c7;
        }

        .mono {
            font-family: DejaVu Sans Mono, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            white-space: nowrap;
        }

        .time { width: 64px; }
        .dur { width: 62px; }
        .place { width: 72px; }
        .block { width: 90px; }

        .muted { color: #cbd5e1; }
    </style>
</head>
<body>
    <h1>Program konferencie</h1>
    <p class="note"><span class="accent">Poznámka:</span> Organizátori si vyhradzujú právo zmeny programu.</p>

    <table>
        <thead>
            <tr>
                <th class="time">Čas</th>
                <th class="dur">Trvanie</th>
                <th>Prednášajúci</th>
                <th>Inštitúcia</th>
                <th>Názov príspevku</th>
                <th class="place">Forma</th>
                <th class="block">Blok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $i => $r)
                @php
                    $isBreak = $r->participation_type === 'break';
                    $timeVal = $r->time_start ? \Illuminate\Support\Carbon::parse($r->time_start)->format('H:i') : '—';
                    $durVal = (int) ($r->duration_minutes ?? 0);
                    $speaker = trim(implode(' ', array_filter([
                        $r->title_before,
                        $r->name,
                    ])));
                    if ($r->title_after) {
                        $speaker = $speaker !== '' ? ($speaker . ', ' . $r->title_after) : $r->title_after;
                    }
                    $form = $r->online_participation ? 'Online' : 'Prezenčne';
                    $blockLabel = match ((string) $r->block) {
                        'intro' => 'Úvod',
                        'teaching' => 'Blok 1',
                        'practice' => 'Blok 2',
                        'students' => 'Blok 3',
                        default => '—',
                    };
                    $rowClass = $i % 2 === 1 ? 'row-alt' : '';
                @endphp

                @if($isBreak)
                    <tr class="break">
                        <td class="mono time">{{ $timeVal }}</td>
                        <td class="mono dur">{{ $durVal }} min</td>
                        <td colspan="5">{{ $r->title ?? 'Prestávka' }}</td>
                    </tr>
                @else
                    <tr class="{{ $rowClass }}">
                        <td class="mono time">{{ $timeVal }}</td>
                        <td class="mono dur">{{ $durVal }} min</td>
                        <td>{{ $speaker !== '' ? $speaker : '—' }}</td>
                        <td class="muted">{{ $r->institution ?? '—' }}</td>
                        <td><span class="accent">{{ $r->title ?? '—' }}</span></td>
                        <td class="place">{{ $form }}</td>
                        <td class="block">{{ $blockLabel }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>

