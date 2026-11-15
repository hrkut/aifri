<?php

declare(strict_types=1);

use App\Models\Conference;
use App\Models\Registration;
use App\Models\User;

function programItem(string $title, string $type, int $order, string $time, int $minutes): Registration
{
    return Registration::create([
        'name' => $type === 'break' ? '—' : 'Autor ' . $title,
        'email' => $type === 'break' ? 'break@local' : strtolower($title) . '@example.com',
        'institution' => 'X',
        'participation_type' => $type,
        'review_status' => $type === 'presentation' ? Registration::REVIEW_APPROVED : null,
        'title' => $title,
        'program_order' => $order,
        'time_start' => $time,
        'duration_minutes' => $minutes,
    ]);
}

function programState(): array
{
    return Registration::query()->inProgram()->orderBy('program_order')->get()
        ->map(fn ($r) => $r->title . ' ' . substr((string) $r->time_start, 0, 5))
        ->all();
}

beforeEach(function () {
    Conference::active()->update(['start_time' => '09:00', 'presentation_minutes' => 15, 'break_minutes' => 20]);
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    programItem('A', 'presentation', 1, '09:00', 15);
    $this->break = programItem('Br', 'break', 2, '09:15', 20);
    programItem('C', 'presentation', 3, '09:35', 15);
    programItem('D', 'presentation', 4, '09:50', 15);
    programItem('E', 'presentation', 5, '10:05', 15);
});

it('moves a break to a later slot and recalculates the whole programme', function () {
    $this->put(route('admin.breaks.update', $this->break), [
        'time_start' => '09:50', 'title' => 'Br', 'duration_minutes' => 20,
    ])->assertRedirect(route('admin.program'));

    expect(programState())->toBe(['A 09:00', 'C 09:15', 'Br 09:30', 'D 09:50', 'E 10:05']);
});

it('moves a break to an earlier slot', function () {
    $this->put(route('admin.breaks.update', $this->break), [
        'time_start' => '09:00', 'title' => 'Br', 'duration_minutes' => 20,
    ]);

    expect(programState())->toBe(['Br 09:00', 'A 09:20', 'C 09:35', 'D 09:50', 'E 10:05']);
});

it('changes the length of a break in place', function () {
    $this->put(route('admin.breaks.update', $this->break), [
        'time_start' => '09:15', 'title' => 'Obed', 'duration_minutes' => 60,
    ]);

    expect(programState())->toBe(['A 09:00', 'Obed 09:15', 'C 10:15', 'D 10:30', 'E 10:45']);
});

it('sets the talk length without touching break lengths unless asked', function () {
    $this->break->update(['duration_minutes' => 60]);
    $pending = Registration::create([
        'name' => 'P', 'email' => 'p@example.com', 'institution' => 'X', 'participation_type' => 'presentation',
        'review_status' => Registration::REVIEW_PENDING, 'title' => 'P', 'duration_minutes' => 15,
    ]);

    $this->post(route('admin.program.duration.apply'), [
        'start_time' => '10:00', 'presentation_minutes' => 20, 'break_minutes' => 30,
    ])->assertRedirect(route('admin.program'));

    expect(programState())->toBe(['A 10:00', 'Br 10:20', 'C 11:20', 'D 11:40', 'E 12:00'])
        ->and($pending->fresh()->duration_minutes)->toBe(20)
        ->and(Conference::active()->break_minutes)->toBe(30);

    $this->post(route('admin.program.duration.apply'), [
        'start_time' => '10:00', 'presentation_minutes' => 20, 'break_minutes' => 30, 'apply_to_breaks' => '1',
    ]);

    expect(programState())->toBe(['A 10:00', 'Br 10:20', 'C 10:50', 'D 11:10', 'E 11:30']);
});
