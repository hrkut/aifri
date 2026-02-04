<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RegistrationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var Builder<Registration> */
    protected Builder $query;

    /**
     * @param Builder<Registration> $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return Builder<Registration>
     */
    public function query(): Builder
    {
        return $this->query->select([
            'id',
            'title_before',
            'name',
            'title_after',
            'email',
            'phone',
            'institution',
            'participation_type',
            'online_participation',
            'title',
            'block',
        ]);
    }

    public function headings(): array
    {
        return [
            'id',
            'title_before',
            'name',
            'title_after',
            'email',
            'phone',
            'institution',
            'participation_type',
            'online_participation',
            'title',
            'block',
        ];
    }

    /**
     * @param Registration $registration
     */
    public function map($registration): array
    {
        return [
            $registration->id,
            $registration->title_before,
            $registration->name,
            $registration->title_after,
            $registration->email,
            $registration->phone,
            $registration->institution,
            // Typ účasti: Aktívna/Pasívna (podľa modelu: presentation=aktívna, passive=pasívna)
            $registration->participation_type === 'presentation' ? 'Aktívna' : 'Pasívna',
            // Forma účasti: Online/Prezenčne (podľa online_participation boolean)
            $registration->online_participation ? 'Online' : 'Prezenčne',
            $registration->title,
            $registration->block,
        ];
    }
}
