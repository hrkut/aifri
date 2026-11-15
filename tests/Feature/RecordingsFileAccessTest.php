<?php

declare(strict_types=1);

use App\Models\Registration;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->dir = storage_path('app/private/zaznamy');
    $this->createdDir = ! is_dir($this->dir);
    File::ensureDirectoryExists($this->dir);

    $this->file = 'Testovací Autor, FRI UNIZA.mp4';
    File::put($this->dir . '/' . $this->file, 'video');
    File::put($this->dir . '/neznamy.mp4', 'not linked to any talk');

    Registration::create([
        'name' => 'Testovací Autor', 'email' => 't@example.com', 'institution' => 'FRI',
        'participation_type' => 'presentation', 'review_status' => Registration::REVIEW_APPROVED,
        'time_start' => '09:00', 'record' => $this->file,
    ]);

    $this->post('/zaznamy', ['password' => 'test-recordings-password']);
});

afterEach(function () {
    File::delete([$this->dir . '/' . $this->file, $this->dir . '/neznamy.mp4']);
    if ($this->createdDir) {
        File::deleteDirectory($this->dir);
    }
});

it('serves a recording that belongs to a talk', function () {
    $this->get(route('recordings.download', $this->file))->assertOk();
});

it('blocks path traversal out of the recordings directory', function (string $path) {
    $this->get('/zaznamy/record/' . $path)->assertNotFound();
    $this->get('/zaznamy/presentation/' . $path)->assertNotFound();
})->with([
    'encoded slashes' => '..%2f..%2f..%2f..%2fcomposer.json',
    'double encoded' => '..%252f..%252f..%252f..%252fcomposer.json',
    'env file' => '..%2f..%2f..%2f..%2f.env',
]);

it('does not serve files that are not linked to any talk', function () {
    $this->get(route('recordings.download', 'neznamy.mp4'))->assertNotFound();
});

it('stays inside the directory even if a bad name is stored in the database', function () {
    Registration::create([
        'name' => 'Zly', 'email' => 'z@example.com', 'institution' => 'X',
        'participation_type' => 'presentation', 'record' => '../../../../composer.json',
    ]);

    $this->get('/zaznamy/record/..%2f..%2f..%2f..%2fcomposer.json')->assertNotFound();
});
