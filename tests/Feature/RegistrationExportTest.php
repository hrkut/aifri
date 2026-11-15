<?php

use App\Exports\RegistrationsExport;
use App\Models\Registration;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

function participant(string $name, array $attrs = []): Registration
{
    return Registration::create($attrs + [
        'name' => $name,
        'email' => \Illuminate\Support\Str::slug($name) . '@example.com',
        'institution' => 'UNIZA',
        'participation_type' => 'passive',
        'online_participation' => false,
    ]);
}

it('exports readable Slovak columns', function () {
    $this->freezeTime();
    participant('Ján Autor', [
        'participation_type' => 'presentation', 'title' => 'AI v triede', 'block' => 'teaching', 'phone' => '0905123456',
    ])->reject('Mimo témy');

    Excel::fake();
    $this->actingAs($this->admin)->get(route('admin.registrations.export'));

    Excel::assertDownloaded('registrations_2027_' . local_now()->format('Y-m-d_His') . '.xlsx', function (RegistrationsExport $export) {
        $row = array_combine($export->headings(), $export->map($export->query()->first()));

        return $row['Meno a priezvisko'] === 'Ján Autor'
            && $row['Typ účasti'] === 'Aktívna'
            && $row['Blok'] === 'Blok 1 – AI vo výučbe a výskume'
            && $row['Stav posúdenia'] === 'Odmietnutý'
            && $row['Zdôvodnenie zamietnutia'] === 'Mimo témy';
    });

});

it('keeps phone numbers as text in the xlsx file', function () {
    participant('Ján Autor', ['phone' => '0905123456']);

    $file = Excel::raw(new RegistrationsExport(Registration::query()), \Maatwebsite\Excel\Excel::XLSX);
    $path = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($path, $file);
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();
    unlink($path);

    expect($sheet->getCell('F2')->getValue())->toBe('0905123456')
        ->and($sheet->getCell('C1')->getValue())->toBe('Meno a priezvisko');
});

it('lists in-person participants alphabetically by surname in the attendance list', function () {
    participant('Peter Šimko');
    participant('Anna Čierna');
    participant('Mária Cibulková');
    participant('Online Hosť', ['online_participation' => true]);
    Registration::create(['name' => '—', 'email' => 'break@local', 'institution' => '—', 'participation_type' => 'break']);

    $data = null;
    \Illuminate\Support\Facades\View::creator('admin.attendance-pdf', function ($view) use (&$data) {
        $data = $view->getData();
    });

    $response = $this->actingAs($this->admin)
        ->get(route('admin.registrations.attendance_pdf', ['online_participation' => 'in_person']));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');

    // Slovak order: C < Č < … < S < Š; online participants and breaks are left out
    expect($data['registrations']->pluck('name')->all())
        ->toBe(['Mária Cibulková', 'Anna Čierna', 'Peter Šimko']);

    // the sheet goes round the room: names and institutions only, no e-mail addresses
    expect(view('admin.attendance-pdf', $data)->render())
        ->toContain('Anna Čierna')
        ->not->toContain('@example.com');
});
