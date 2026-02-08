<?php

declare(strict_types=1);

use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

it('stores title_before and title_after into DB', function () {
    Mail::fake();

    $response = $this->post(route('registration.submit'), [
        'title_before' => 'Dr.',
        'name' => 'Test User',
        'title_after' => 'PhD.',
        'email' => 'test@example.com',
        'phone' => '0900123456',
        'institution' => 'UNIZA',
        'position' => 'Researcher',
        'notes' => 'hello',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'email' => 'test@example.com',
        'title_before' => 'Dr.',
        'title_after' => 'PhD.',
    ]);

    /** @var Registration $registration */
    $registration = Registration::where('email', 'test@example.com')->firstOrFail();
    expect($registration->title_before)->toBe('Dr.');
    expect($registration->title_after)->toBe('PhD.');
});

