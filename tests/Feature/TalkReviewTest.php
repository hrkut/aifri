<?php

declare(strict_types=1);

use App\Models\Conference;
use App\Models\Registration;
use App\Models\User;
use App\Mail\TalkApproved;
use App\Mail\TalkRejected;
use Illuminate\Support\Facades\Mail;

function talk(string $title, array $attrs = []): Registration
{
    return Registration::create($attrs + [
        'name' => 'Autor ' . $title,
        'email' => strtolower($title) . '@example.com',
        'institution' => 'UNIZA',
        'participation_type' => 'presentation',
        'title' => $title,
        'duration_minutes' => 15,
        'review_status' => Registration::REVIEW_PENDING,
    ]);
}

beforeEach(function () {
    Mail::fake();
    Conference::active()->update(['start_time' => '09:00']);
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('keeps pending and rejected talks out of the programme', function () {
    talk('Cakajuci');
    talk('Zamietnuty', ['review_status' => Registration::REVIEW_REJECTED]);
    talk('Schvaleny', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:00', 'program_order' => 1]);

    expect(Registration::inProgram()->pluck('title')->all())->toBe(['Schvaleny']);

    $this->actingAs($this->admin)->get(route('admin.program'))
        ->assertSee('Schvaleny')->assertDontSee('Cakajuci')->assertDontSee('Zamietnuty');
});

it('approves a talk to the end of the programme with recalculated times', function () {
    $first = talk('Prvy', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:00', 'program_order' => 1]);
    $new = talk('Novy');

    $this->actingAs($this->admin)
        ->post(route('admin.registration.approve', $new))
        ->assertRedirect()
        ->assertSessionHas('success');

    $new->refresh();
    expect($new->review_status)->toBe(Registration::REVIEW_APPROVED)
        ->and($new->reviewed_at)->not->toBeNull()
        ->and($new->program_order)->toBe(2)
        ->and(substr((string) $new->time_start, 0, 5))->toBe('09:15')
        ->and(substr((string) $first->fresh()->time_start, 0, 5))->toBe('09:00');
});

it('rejects a talk, removes it from the programme and closes the gap', function () {
    $a = talk('A', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:00', 'program_order' => 1]);
    $b = talk('B', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:15', 'program_order' => 2]);
    $c = talk('C', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:30', 'program_order' => 3]);

    $this->actingAs($this->admin)
        ->post(route('admin.registration.reject', $b), ['review_comment' => 'Téma nezapadá do zamerania blokov.'])
        ->assertRedirect();

    $b->refresh();
    expect($b->review_status)->toBe(Registration::REVIEW_REJECTED)
        ->and($b->time_start)->toBeNull()
        ->and($b->program_order)->toBeNull()
        ->and(substr((string) $c->fresh()->time_start, 0, 5))->toBe('09:15')
        ->and($c->fresh()->program_order)->toBe(2);

    $this->get(route('program'))->assertDontSee('>B<', false);
});

it('shows review state, filter and actions with tooltips on the dashboard', function () {
    $pending = talk('Cakajuci');
    $approved = talk('Schvaleny', ['review_status' => Registration::REVIEW_APPROVED]);
    Registration::create(['name' => 'Posluchac', 'email' => 'p@example.com', 'institution' => 'X', 'participation_type' => 'passive']);

    $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Čaká na posúdenie')
        ->assertSee('Schválený')
        ->assertSee('role="tooltip"', false)
        ->assertSee('Zobraziť detaily')
        ->assertSee(route('admin.registration.approve', $pending))
        ->assertSee(route('admin.registration.reject', $pending))
        ->assertSee(route('admin.registration.reject', $approved))
        ->assertDontSee(route('admin.registration.approve', $approved))
        ->getContent();

    // one approve action (for the pending talk), each with an accessible name and a tooltip
    expect(substr_count($html, '/approve"'))->toBe(1)
        ->and(substr_count($html, 'aria-label="Schváliť"'))->toBe(1)
        ->and(substr_count($html, 'aria-label="Odmietnuť"'))->toBe(2);

    $this->get(route('admin.dashboard', ['review' => 'pending']))
        ->assertSee('Cakajuci')->assertDontSee('Schvaleny')->assertDontSee('Posluchac');
});

it('does not review passive participants and is blocked in archived editions', function () {
    $passive = Registration::create(['name' => 'P', 'email' => 'p@example.com', 'institution' => 'X', 'participation_type' => 'passive']);
    $this->actingAs($this->admin)->post(route('admin.registration.approve', $passive))->assertNotFound();

    $archive = Conference::where('year', 2026)->firstOrFail();
    $old = Registration::unguarded(fn () => Registration::create([
        'conference_id' => $archive->id, 'name' => 'Stary', 'email' => 's@example.com', 'institution' => 'X',
        'participation_type' => 'presentation', 'title' => 'Stary', 'review_status' => Registration::REVIEW_PENDING,
    ]));

    $this->post(route('admin.conferences.select', $archive));
    $this->post(route('admin.registration.approve', $old))->assertRedirect(route('admin.dashboard'))->assertSessionHas('error');
    expect($old->fresh()->review_status)->toBe(Registration::REVIEW_PENDING);

    $this->get(route('admin.dashboard'))->assertDontSee(route('admin.registration.approve', $old));
});

it('emails the author when a talk is approved', function () {
    $new = talk('Novy', ['title_before' => 'Ing.', 'email' => 'autor@example.com']);

    $this->actingAs($this->admin)->post(route('admin.registration.approve', $new))
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'E-mail autorovi (autor@example.com) bol odoslaný'));

    Mail::assertSent(TalkApproved::class, function ($mail) {
        $mail->assertHasSubject('Výsledok posúdenia príspevku – Konferencia o AI vo vzdelávaní 2027');
        $mail->assertSeeInHtml('Ing. Autor Novy');
        $mail->assertSeeInHtml('Novy');
        $mail->assertDontSeeInHtml('Predbežný čas');
        $mail->assertSeeInText('Čas vášho vystúpenia vám oznámime po zostavení programu');
        $mail->assertSeeInText('prijal váš príspevok');

        return $mail->hasTo('autor@example.com') && $mail->hasReplyTo('konferenciaAI@fri.uniza.sk');
    });
    Mail::assertNotSent(TalkRejected::class);
});

it('requires a reason to reject and sends it to the author', function () {
    $talk = talk('Odmietnuty', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:00', 'program_order' => 1, 'email' => 'autor@example.com']);

    // no or too short reason: nothing happens
    $this->actingAs($this->admin)->post(route('admin.registration.reject', $talk))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'zdôvodnenie'));
    $this->post(route('admin.registration.reject', $talk), ['review_comment' => 'kratke'])
        ->assertSessionHas('error');
    expect($talk->fresh()->review_status)->toBe(Registration::REVIEW_APPROVED);
    Mail::assertNothingSent();

    $reason = "Téma sa prekrýva s pozvanou prednáškou.\nOdporúčame prihlásiť ju na budúci ročník.";
    $this->post(route('admin.registration.reject', $talk), ['review_comment' => $reason])
        ->assertSessionHas('success');

    $talk->refresh();
    expect($talk->review_status)->toBe(Registration::REVIEW_REJECTED)
        ->and($talk->review_comment)->toBe($reason);

    Mail::assertSent(TalkRejected::class, function ($mail) {
        $mail->assertHasSubject('Výsledok posúdenia príspevku – Konferencia o AI vo vzdelávaní 2027');
        $mail->assertSeeInHtml('❌ Príspevok neprijatý');
        $mail->assertSeeInHtml('Zdôvodnenie programového výboru');
        $mail->assertSeeInHtml('Téma sa prekrýva s pozvanou prednáškou.');
        $mail->assertSeeInText('Odporúčame prihlásiť ju na budúci ročník.');
        $mail->assertSeeInText('vaša registrácia zostáva platná');

        return $mail->hasTo('autor@example.com');
    });

    // the reason is visible in the admin detail; re-approving clears it
    $this->get(route('admin.registration.show', $talk))->assertSee('Zdôvodnenie zaslané autorovi');
    $this->post(route('admin.registration.approve', $talk));
    expect($talk->fresh()->review_comment)->toBeNull();
});

it('renders the reject dialog with a required reason field', function () {
    $talk = talk('Cakajuci');

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertSee('<dialog id="reject-dialog-' . $talk->id . '"', false)
        ->assertSee('name="review_comment"', false)
        ->assertSee('required minlength="10"', false)
        ->assertSee('Zamietnuť a poslať e-mail');
});

it('keeps the registration when a mail cannot be sent', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));
    $talk = talk('Bez mailu');

    $this->actingAs($this->admin)->post(route('admin.registration.approve', $talk))
        ->assertSessionHas('success', fn ($m) => str_contains($m, 'nepodarilo odoslať'));

    expect($talk->fresh()->review_status)->toBe(Registration::REVIEW_APPROVED);
});

it('renders the approve dialog without a programme time', function () {
    talk('Prvy', ['review_status' => Registration::REVIEW_APPROVED, 'time_start' => '09:00', 'program_order' => 1, 'duration_minutes' => 20]);
    $pending = talk('Cakajuci', ['email' => 'autor@example.com']);

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertSee('<dialog id="approve-dialog-' . $pending->id . '"', false)
        ->assertSee('Schváliť a poslať e-mail')
        ->assertSee('e-mail o prijatí na autor@example.com')
        ->assertDontSee('predbežne')
        ->assertDontSee('confirm(', false);
});

it('does not list programme breaks as registrations', function () {
    $this->freezeTime(); // the export file name contains the current time
    Registration::create(['name' => '—', 'email' => 'break@local', 'institution' => '—', 'participation_type' => 'break', 'title' => 'Prestávka na obed']);
    Registration::create(['name' => 'Účastník', 'email' => 'u@example.com', 'institution' => 'X', 'participation_type' => 'passive']);

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertSee('u@example.com')
        ->assertDontSee('break@local');

    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->get(route('admin.registrations.export'));
    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('registrations_2027_' . now()->format('Y-m-d_His') . '.xlsx', function ($export) {
        $emails = $export->query()->pluck('email')->all();

        return $emails === ['u@example.com'];
    });
});
