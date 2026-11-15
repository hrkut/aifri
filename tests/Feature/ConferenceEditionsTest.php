<?php

declare(strict_types=1);

use App\Models\Conference;
use App\Models\Question;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function makeRegistration(array $attrs = []): Registration
{
    return Registration::create($attrs + [
        'name' => 'Test',
        'email' => 'test@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
        'online_participation' => true,
        'time_start' => '09:00',
    ]);
}

/** Registration in a specific (non-current) edition; conference_id is intentionally not mass-assignable. */
function archivedRegistration(array $attrs): Registration
{
    return Registration::unguarded(fn () => Registration::create($attrs));
}

it('migrates into a 2026 archive edition and an active 2027 edition', function () {
    $active = Conference::active();

    expect($active->year)->toBe(2027)
        ->and($active->date->format('Y-m-d'))->toBe('2027-02-02')
        ->and($active->dateLabel())->toBe('2. 2. 2027 (utorok) od 9:00')
        ->and(Conference::where('year', 2026)->value('is_active'))->toBeFalse();
});

it('scopes registrations and questions to the current edition', function () {
    $archive = Conference::where('year', 2026)->firstOrFail();

    $old = archivedRegistration([
        'conference_id' => $archive->id,
        'name' => 'Old',
        'email' => 'old@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
    ]);
    $new = makeRegistration(['name' => 'New']);
    Question::create(['name' => 'Q', 'question' => 'Otázka?']);

    expect($new->conference_id)->toBe(Conference::active()->id)
        ->and(Registration::pluck('id')->all())->toBe([$new->id])
        ->and(Question::withoutGlobalScope('conference')->value('conference_id'))->toBe(Conference::active()->id);

    // Archived registration is not reachable through the admin of the active edition
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get(route('admin.registration.show', $old->id))->assertNotFound();
});

it('lets an admin switch the working edition without changing the public site', function () {
    $archive = Conference::where('year', 2026)->firstOrFail();
    archivedRegistration([
        'conference_id' => $archive->id,
        'name' => 'Archívny Účastník',
        'email' => 'old@example.com',
        'institution' => 'X',
        'participation_type' => 'passive',
    ]);

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee('Archívny Účastník');

    $this->actingAs($admin)->post(route('admin.conferences.select', $archive))->assertRedirect(route('admin.dashboard'));

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertSee('Archívny Účastník')
        ->assertSee('len na čítanie');

    $this->get(route('home'))->assertSee('2. 2. 2027 (utorok) od 9:00');
});

it('creates, updates and activates an edition', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post(route('admin.conferences.store'), [
        'year' => 2028,
        'date' => '2028-02-09',
        'registration_deadline' => '2028-02-01',
        'start_time' => '10:00',
        'presentation_minutes' => 20,
        'break_minutes' => 15,
    ])->assertRedirect(route('admin.conferences.index'));

    $c = Conference::where('year', 2028)->firstOrFail();
    expect($c->is_active)->toBeFalse()->and($c->presentation_minutes)->toBe(20);

    $this->actingAs($admin)->put(route('admin.conferences.update', $c), [
        'year' => 2028,
        'date' => '2028-02-09',
        'registration_deadline' => '2028-03-01',
    ])->assertSessionHasErrors('registration_deadline');

    $this->actingAs($admin)->put(route('admin.conferences.update', $c), [
        'year' => 2028,
        'date' => '2028-02-09',
        'teams_url' => 'https://teams.microsoft.com/l/meetup-join/abc',
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->post(route('admin.conferences.activate', $c));

    expect(Conference::active()->id)->toBe($c->id)
        ->and(Conference::where('is_active', true)->count())->toBe(1);
});

it('shows edition data on public pages', function () {
    Conference::active()->update([
        'registration_deadline' => now()->addMonth()->toDateString(),
        'teams_url' => 'https://teams.microsoft.com/l/meetup-join/new-edition',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('2. 2. 2027 (utorok) od 9:00')
        ->assertSee('new-edition')
        ->assertSee(route('registration'))
        ->assertSee('2. ročník');

    $this->get(route('registration'))->assertOk()->assertSee('2. 2. 2027');
    $this->get(route('program'))->assertOk()->assertSee('new-edition');
});

it('hides registration and Teams links when not applicable', function () {
    Conference::active()->update([
        'date' => now()->subDay()->toDateString(),
        'registration_deadline' => null,
        'teams_url' => null,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Registrácia na konferenciu')
        ->assertDontSee('teams.microsoft.com');
});

it('lists recordings of all past editions on one page', function () {
    $archive = Conference::where('year', 2026)->firstOrFail();
    archivedRegistration([
        'conference_id' => $archive->id,
        'name' => 'Archívny Prednášajúci',
        'email' => 'old@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
        'time_start' => '09:00',
    ]);
    makeRegistration(['name' => 'Nový Prednášajúci']);

    $this->post('/zaznamy', ['password' => 'test-recordings-password']);

    // 2027 has not taken place yet -> only the archive is listed
    $this->get(route('recordings.list'))
        ->assertOk()
        ->assertSee('1. ročník')
        ->assertSee('11. 2. 2026')
        ->assertSee('Archívny Prednášajúci')
        ->assertDontSee('Nový Prednášajúci')
        ->assertDontSee('class="edition-nav"', false);

    // On the conference day the new edition joins the list, newest first
    Conference::active()->update(['date' => now()->toDateString()]);

    $this->get(route('recordings.list'))
        ->assertSeeInOrder(['2. ročník', 'Nový Prednášajúci', '1. ročník', 'Archívny Prednášajúci'])
        ->assertSee('href="#rocnik-2026"', false);
});

it('stores public registrations in the active edition', function () {
    Mail::fake();

    $this->post(route('registration.submit'), [
        'name' => 'Nový Účastník',
        'email' => 'n@example.com',
        'institution' => 'UNIZA',
        'participation_type' => 'passive',
    ])->assertRedirect();

    expect(Registration::withoutGlobalScope('conference')->where('email', 'n@example.com')->value('conference_id'))
        ->toBe(Conference::active()->id);
});

it('renders the admin editions pages', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $c = Conference::active();

    $this->actingAs($admin)->get(route('admin.conferences.index'))->assertOk()->assertSee('2027')->assertSee('2026');
    $this->actingAs($admin)->get(route('admin.conferences.create'))->assertOk()->assertSee('value="2028"', false);
    $this->actingAs($admin)->get(route('admin.conferences.edit', $c))->assertOk()->assertSee('value="2027-02-02"', false);
    $this->actingAs($admin)->get(route('admin.program'))->assertOk();
    $this->actingAs($admin)->get(route('admin.questions'))->assertOk();
});

it('shows archived editions read-only in admin', function () {
    $archive = Conference::where('year', 2026)->firstOrFail();
    $talk = archivedRegistration([
        'conference_id' => $archive->id,
        'name' => 'Archívny Prednášajúci',
        'email' => 'old@example.com',
        'institution' => 'X',
        'participation_type' => 'presentation',
        'review_status' => Registration::REVIEW_APPROVED,
        'time_start' => '09:00',
        'duration_minutes' => 15,
        'program_order' => 1,
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->post(route('admin.conferences.select', $archive));

    // Browsing works
    $this->get(route('admin.dashboard'))->assertOk()->assertSee('Archívny Prednášajúci')->assertSee('len na čítanie');
    $this->get(route('admin.registration.show', $talk))->assertOk();
    $this->get(route('admin.program'))
        ->assertOk()
        ->assertSee('Archívny Prednášajúci')
        ->assertSee('data-program-sortable="false"', false)
        ->assertDontSee(route('admin.program.create'))
        ->assertDontSee(route('admin.registration.edit', ['registration' => $talk->id, 'return' => 'program']))
        ->assertDontSee(route('admin.program.destroy', $talk))
        ->assertDontSee('data-program-duration-input', false);

    // Changes are blocked server-side
    $this->get(route('admin.registration.edit', $talk))->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.program.create'))->assertRedirect(route('admin.dashboard'));
    $this->delete(route('admin.program.destroy', $talk))->assertRedirect(route('admin.dashboard'))->assertSessionHas('error');
    $this->postJson(route('admin.program.reorder'), ['ids' => [$talk->id]])->assertForbidden();
    $this->postJson(route('admin.program.duration.update', $talk), ['duration_minutes' => 30])->assertForbidden();

    expect(Registration::withoutGlobalScope('conference')->find($talk->id)->duration_minutes)->toBe(15);

    // Switching back to the active edition is still possible
    $this->post(route('admin.conferences.select', Conference::active()))->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.program'))->assertSee(route('admin.program.create'));
});

it('keeps unpublished future editions editable', function () {
    $future = Conference::create(['year' => 2028, 'date' => '2028-02-09', 'start_time' => '09:00', 'presentation_minutes' => 15, 'break_minutes' => 20]);
    $admin = User::factory()->create(['is_admin' => true]);

    expect($future->isReadOnly())->toBeFalse();

    $this->actingAs($admin)->post(route('admin.conferences.select', $future));
    $this->get(route('admin.program.create'))->assertOk();
});

it('sends a themed confirmation to a passive participant', function () {
    Mail::fake();
    Conference::active()->update(['teams_url' => 'https://teams.microsoft.com/l/meetup-join/xyz']);

    $this->post(route('registration.submit'), [
        'title_before' => 'Ing.',
        'name' => 'Jana Testová',
        'title_after' => 'PhD.',
        'email' => 'jana@example.com',
        'institution' => 'UNIZA',
        'participation_type' => 'passive',
    ])->assertRedirect();

    Mail::assertSent(\App\Mail\RegistrationConfirmation::class, function ($mail) {
        if (! $mail->hasTo('jana@example.com')) {
            return false;
        }

        $mail->assertHasSubject('Potvrdenie registrácie – Konferencia o AI vo vzdelávaní 2027');
        $mail->assertSeeInHtml('Ing. Jana Testová, PhD.');
        $mail->assertSeeInHtml('2. 2. 2027 (utorok) od 9:00');
        $mail->assertSeeInHtml('pasívneho účastníka');
        $mail->assertSeeInHtml('meetup-join/xyz', false);
        $mail->assertSeeInText('potvrdzujeme');

        return true;
    });
    Mail::assertSent(\App\Mail\NewRegistrationNotification::class, function ($mail) {
        $mail->assertSeeInHtml('Nová registrácia');
        $mail->assertSeeInHtml('Ing. Jana Testová, PhD.');
        $mail->assertSeeInHtml('2. ročník');
        $mail->assertSeeInHtml('pasívna účasť');
        $mail->assertSeeInText('jana@example.com');

        return $mail->hasTo('konferenciaAI@fri.uniza.sk') && $mail->hasReplyTo('jana@example.com');
    });
});

it('registers an active participant with a contribution', function () {
    Mail::fake();
    Conference::active()->update(['notification_date' => '2027-01-25']);

    $this->post(route('registration.submit'), [
        'name' => 'Peter Prednášajúci',
        'email' => 'peter@example.com',
        'institution' => 'UNIZA',
        'participation_type' => 'presentation',
        'title' => 'AI v laboratórnych cvičeniach',
        'abstract' => 'Krátky abstrakt príspevku.',
        'keywords' => 'AI, cvičenia',
        'block' => 'practice',
    ])->assertSessionHasNoErrors()->assertRedirect(route('registration') . '#registration-form');

    $reg = Registration::where('email', 'peter@example.com')->firstOrFail();
    expect($reg->participation_type)->toBe('presentation')
        ->and($reg->title)->toBe('AI v laboratórnych cvičeniach')
        ->and($reg->block)->toBe('practice')
        ->and($reg->online_participation)->toBeFalse()
        ->and($reg->time_start)->toBeNull()
        ->and($reg->duration_minutes)->toBe(15);

    Mail::assertSent(\App\Mail\RegistrationConfirmation::class, function ($mail) {
        $mail->assertSeeInHtml('aktívneho účastníka');
        $mail->assertSeeInHtml('AI v laboratórnych cvičeniach');
        $mail->assertSeeInHtml('Blok 2 – AI v praxi');
        $mail->assertSeeInHtml('25. 1. 2027');
        $mail->assertSeeInText('Príspevok teraz posúdi programový výbor');

        return $mail->hasTo('peter@example.com');
    });
    Mail::assertSent(\App\Mail\NewRegistrationNotification::class, fn ($mail) => $mail->hasTo('konferenciaAI@fri.uniza.sk'));

    // Waits for review: not in the programme until the organisers approve it
    expect($reg->reviewStatus())->toBe(Registration::REVIEW_PENDING);
    $this->get(route('program'))->assertDontSee('AI v laboratórnych cvičeniach');

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->post(route('admin.registration.approve', $reg))->assertRedirect();
    $this->get(route('program'))->assertSee('AI v laboratórnych cvičeniach');
});

it('requires title and abstract for active participation and limits the abstract', function () {
    Mail::fake();
    $base = ['name' => 'P', 'email' => 'p@example.com', 'institution' => 'X', 'participation_type' => 'presentation'];

    $this->post(route('registration.submit'), $base)->assertSessionHasErrors(['title', 'abstract']);

    $this->post(route('registration.submit'), $base + [
        'title' => 'T',
        'abstract' => trim(str_repeat('slovo ', Registration::ABSTRACT_MAX_WORDS + 1)),
    ])->assertSessionHasErrors('abstract');

    $this->post(route('registration.submit'), ['name' => 'P', 'email' => 'p@example.com', 'institution' => 'X'])
        ->assertSessionHasErrors('participation_type');

    expect(Registration::count())->toBe(0);
    Mail::assertNothingSent();
});

it('drops contribution fields for passive participants', function () {
    Mail::fake();

    $this->post(route('registration.submit'), [
        'name' => 'P', 'email' => 'p@example.com', 'institution' => 'X',
        'participation_type' => 'passive',
        'title' => 'Zabudnutý názov', 'abstract' => 'Text', 'block' => 'intro',
    ])->assertSessionHasNoErrors();

    $reg = Registration::firstOrFail();
    expect($reg->title)->toBeNull()->and($reg->abstract)->toBeNull()->and($reg->block)->toBeNull();
});

it('shows both participation types on the registration form', function () {
    $this->get(route('registration'))
        ->assertOk()
        ->assertSee('Aktívna účasť – prednáška')
        ->assertSee('Pasívna účasť (poslucháč)')
        ->assertSee('name="online_participation"', false)
        ->assertSee('Názov príspevku *');
});

it('closes active registration after the contribution deadline while passive stays open', function () {
    Mail::fake();
    $this->travelTo('2027-01-20 12:00');
    Conference::active()->update([
        'contribution_deadline' => '2027-01-15',
        'registration_deadline' => '2027-01-31',
    ]);

    $this->get(route('registration'))
        ->assertOk()
        ->assertSee('Prihlasovanie príspevkov je uzatvorené')
        ->assertSee('15. 1. 2027 – Deadline na príspevky (aktívna účasť)')
        ->assertSee('31. 1. 2027 – Deadline na registráciu (pasívna účasť)');

    $this->post(route('registration.submit'), [
        'name' => 'Neskorý Autor', 'email' => 'late@example.com', 'institution' => 'X',
        'participation_type' => 'presentation', 'title' => 'T', 'abstract' => 'A',
    ])->assertSessionHasErrors('participation_type');

    $this->post(route('registration.submit'), [
        'name' => 'Poslucháč', 'email' => 'listener@example.com', 'institution' => 'X',
        'participation_type' => 'passive',
    ])->assertSessionHasNoErrors();

    expect(Registration::pluck('email')->all())->toBe(['listener@example.com']);
});

it('shows the contribution deadline while it is open', function () {
    $this->travelTo('2027-01-10 12:00');
    Conference::active()->update([
        'contribution_deadline' => '2027-01-15',
        'registration_deadline' => '2027-01-31',
    ]);

    $this->get(route('home'))->assertSee('Príspevky do 15. 1. 2027')->assertSee('Registrácia do 31. 1. 2027');
    $this->get(route('registration'))
        ->assertSee('Príspevky prijímame do 15. 1. 2027')
        ->assertDontSee('prihlasovanie príspevkov je uzatvorené');
});

it('validates the contribution deadline in the edition form', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $c = Conference::active();

    $this->actingAs($admin)->put(route('admin.conferences.update', $c), [
        'year' => 2027, 'date' => '2027-02-02',
        'registration_deadline' => '2027-01-31',
        'contribution_deadline' => '2027-02-01',
    ])->assertSessionHasErrors('contribution_deadline');

    $this->actingAs($admin)->put(route('admin.conferences.update', $c), [
        'year' => 2027, 'date' => '2027-02-02',
        'registration_deadline' => '2027-01-31',
        'contribution_deadline' => '2027-01-22',
    ])->assertSessionHasNoErrors();

    expect($c->fresh()->contribution_deadline->format('Y-m-d'))->toBe('2027-01-22');
    $this->actingAs($admin)->get(route('admin.conferences.edit', $c))->assertSee('value="2027-01-22"', false);
});

it('backfills the 2026 contribution deadline', function () {
    expect(Conference::where('year', 2026)->first()->contribution_deadline->format('Y-m-d'))->toBe('2026-02-04');
});

it('no longer offers block 3 but still labels it for past talks', function () {
    Mail::fake();

    $this->get(route('registration'))->assertOk()->assertDontSee('Blok 3')->assertSee('Blok 2 – AI v praxi');
    $this->get(route('home'))->assertDontSee('Blok 3');

    $this->post(route('registration.submit'), [
        'name' => 'P', 'email' => 'p@example.com', 'institution' => 'X',
        'participation_type' => 'presentation', 'title' => 'T', 'abstract' => 'A', 'block' => 'students',
    ])->assertSessionHasErrors('block');

    $archive = Conference::where('year', 2026)->firstOrFail();
    $old = archivedRegistration([
        'conference_id' => $archive->id, 'name' => 'Študent', 'email' => 's@example.com', 'institution' => 'X',
        'participation_type' => 'presentation', 'block' => 'students', 'time_start' => '09:00',
    ]);
    expect($old->blockLabel())->toBe('Blok 3 – AI očami študentov');

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->post(route('admin.conferences.select', $archive));
    $this->get(route('admin.registration.show', $old))->assertSee('Blok 3 – AI očami študentov');
});

it('renders the shared navigation bar and hero on every public page', function () {
    Conference::active()->update(['registration_deadline' => now()->addMonth()->toDateString()]);

    foreach (['home', 'program', 'registration', 'question', 'committee', 'recordings.login'] as $route) {
        $html = $this->get(route($route))->assertOk()->assertSee('class="siteNav"', false)->getContent();

        // exactly one highlighted item, pointing to the current page
        expect(substr_count($html, 'aria-current="page"'))->toBe(1);
        expect($html)->toMatch('#<a href="' . preg_quote(route($route), '#') . '"\s+aria-current="page"#');
    }

    $this->get(route('home'))->assertSee('Registrovať sa');
    $this->get(route('registration'))->assertDontSee('Registrovať sa');
});

it('closes registration after the registration deadline', function () {
    Mail::fake();
    $this->travelTo('2027-02-01 12:00');
    Conference::active()->update(['registration_deadline' => '2027-01-31', 'contribution_deadline' => '2027-01-20']);

    $this->get(route('registration'))
        ->assertOk()
        ->assertSee('Registrácia na tento ročník konferencie je už uzatvorená.')
        ->assertDontSee('class="regForm"', false);

    $this->post(route('registration.submit'), [
        'name' => 'Neskoro', 'email' => 'late@example.com', 'institution' => 'X', 'participation_type' => 'passive',
    ])->assertRedirect()->assertSessionHasErrors('registration');

    $this->post('/en/registration', [
        'name' => 'Late', 'email' => 'late-en@example.com', 'institution' => 'X', 'participation_type' => 'passive',
    ])->assertSessionHasErrors(['registration' => 'Registration for this edition of the conference is closed.']);

    expect(Registration::count())->toBe(0);
    Mail::assertNothingSent();

    // the deadline day itself is still open
    $this->travelTo('2027-01-31 23:00');
    $this->get(route('registration'))->assertSee('class="regForm"', false);
});
