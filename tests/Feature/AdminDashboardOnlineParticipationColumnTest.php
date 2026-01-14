<?php

use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows online_participation column on admin dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // Create two registrations with different participation forms.
    Registration::create([
        'name' => 'Online User',
        'email' => 'online@example.com',
        'phone' => null,
        'institution' => 'FRI',
        'position' => null,
        'participation_type' => 'passive',
        'online_participation' => true,
        'title_before' => null,
        'title_after' => null,
        'title' => null,
        'abstract' => null,
        'keywords' => null,
        'block' => null,
        'notes' => null,
    ]);

    Registration::create([
        'name' => 'In Person User',
        'email' => 'person@example.com',
        'phone' => null,
        'institution' => 'FRI',
        'position' => null,
        'participation_type' => 'passive',
        'online_participation' => false,
        'title_before' => null,
        'title_after' => null,
        'title' => null,
        'abstract' => null,
        'keywords' => null,
        'block' => null,
        'notes' => null,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Typ účasti');
    $response->assertSee('Forma účasti');

    // Values rendered in the table.
    $response->assertSee('Online');
    $response->assertSee('Prezenčne');
});
