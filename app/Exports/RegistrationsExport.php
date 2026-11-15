<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Registrations of the admin's working edition, filtered like the dashboard table. */
class RegistrationsExport extends DefaultValueBinder implements WithCustomValueBinder, FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    /** @param Builder<Registration> $query */
    public function __construct(protected Builder $query)
    {
    }

    /** @return Builder<Registration> */
    public function query(): Builder
    {
        return $this->query;
    }

    /** Everything except the ID stays text: phone numbers keep their + and leading 0. */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($cell->getColumn() !== 'A' && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function title(): string
    {
        return 'Registrácie';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Titul pred menom',
            'Meno a priezvisko',
            'Titul za menom',
            'E-mail',
            'Telefón',
            'Inštitúcia',
            'Pozícia',
            'Typ účasti',
            'Forma účasti',
            'Názov príspevku',
            'Blok',
            'Kľúčové slová',
            'Abstrakt',
            'Stav posúdenia',
            'Zdôvodnenie zamietnutia',
            'Poznámky',
            'Jazyk',
            'Registrovaný',
        ];
    }

    /** @param Registration $registration */
    public function map($registration): array
    {
        $isTalk = $registration->isTalk();

        return [
            $registration->id,
            $registration->title_before,
            $registration->name,
            $registration->title_after,
            $registration->email,
            $registration->phone,
            $registration->institution,
            $registration->position,
            $isTalk ? 'Aktívna' : 'Pasívna',
            $registration->online_participation ? 'Online' : 'Prezenčne',
            $isTalk ? $registration->title : null,
            $isTalk ? $registration->blockLabel() : null,
            $isTalk ? $registration->keywords : null,
            $isTalk ? $registration->abstract : null,
            $isTalk ? Registration::REVIEW_LABELS[$registration->reviewStatus()] : null,
            $registration->reviewStatus() === Registration::REVIEW_REJECTED ? $registration->review_comment : null,
            $registration->notes,
            $registration->locale === 'en' ? 'angličtina' : 'slovenčina',
            $registration->created_at ? local_time($registration->created_at)->format('j. n. Y H:i') : null,
        ];
    }

    /** Long texts get a fixed width and wrap instead of one very wide column. */
    public function columnWidths(): array
    {
        return ['G' => 45, 'H' => 25, 'K' => 50, 'M' => 30, 'N' => 80, 'P' => 40, 'Q' => 40];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:' . $lastColumn . $sheet->getHighestRow());
                $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB('E2E8F0');
                $sheet->getStyle('A:' . $lastColumn)->getAlignment()->setVertical('top');
                foreach (['G', 'H', 'K', 'M', 'N', 'P', 'Q'] as $column) {
                    $sheet->getStyle($column)->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
