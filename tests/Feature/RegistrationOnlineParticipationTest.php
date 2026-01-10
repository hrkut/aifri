<?php

use App\Mail\NewRegistrationNotification;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('stores online_participation as true when checkbox is checked', function () {
    Mail::fake();

    $payload = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '0900000000',
        'institution' => 'FRI',
        'position' => 'Teacher',
        'participation_type' => 'passive',
        'online_participation' => '1',
    ];

    $response = $this->post(route('registration.submit'), $payload);

    $response->assertRedirect(route('registration') . '#registration-form');

    $this->assertDatabaseHas('registrations', [
        'email' => 'test@example.com',
        'online_participation' => 1,
    ]);

    Mail::assertSent(NewRegistrationNotification::class, function ($mail) {
        return $mail->hasTo('konferenciaAI@fri.uniza.sk');
    });
});

it('stores online_participation as false when checkbox is not present', function () {
    Mail::fake();

    $payload = [
        'name' => 'Test User 2',
        'email' => 'test2@example.com',
        'phone' => null,
        'institution' => 'FRI',
        'position' => null,
        'participation_type' => 'passive',
        // checkbox omitted
    ];

    $response = $this->post(route('registration.submit'), $payload);

    $response->assertRedirect(route('registration') . '#registration-form');

    $reg = Registration::where('email', 'test2@example.com')->firstOrFail();
    expect($reg->online_participation)->toBeFalse();

    Mail::assertSent(NewRegistrationNotification::class, function ($mail) {
        return $mail->hasTo('konferenciaAI@fri.uniza.sk');
    });
});
