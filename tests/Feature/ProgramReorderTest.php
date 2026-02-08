<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('allows admin to reorder program registrations and persists program_order', function () {
    // Create admin
    /** @var User $admin */
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    // Create 3 presentation registrations
    $r1 = Registration::create([
        'name' => 'A',
        'email' => 'a@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
        'online_participation' => true,
    ]);
    $r2 = Registration::create([
        'name' => 'B',
        'email' => 'b@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
        'online_participation' => true,
    ]);
    $r3 = Registration::create([
        'name' => 'C',
        'email' => 'c@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
        'online_participation' => true,
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.program.reorder'), ['ids' => [$r3->id, $r1->id, $r2->id]])
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect(Registration::find($r3->id)?->program_order)->toBe(1);
    expect(Registration::find($r1->id)?->program_order)->toBe(2);
    expect(Registration::find($r2->id)?->program_order)->toBe(3);
});

