<?php

declare(strict_types=1);

use App\Mail\NewRegistrationNotification;
use App\Mail\RegistrationConfirmation;
use App\Mail\TalkApproved;
use App\Mail\TalkRejected;
use App\Models\Conference;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('serves every public page in English under /en', function (string $url, string $text) {
    $this->get($url)
        ->assertOk()
        ->assertSee('<html lang="en">', false)
        ->assertSee($text);
})->with([
    'home' => ['/en', 'Programme of the day'],
    'registration' => ['/en/registration', 'Registration form'],
    'programme' => ['/en/program', 'CONFERENCE PROGRAMME'],
    'committee' => ['/en/committee', 'Programme and organising committee'],
    'question' => ['/en/question', 'Ask your question'],
    'recordings' => ['/en/recordings', 'Access to recordings'],
]);

it('keeps the Slovak pages unchanged', function () {
    $this->get('/')->assertOk()->assertSee('<html lang="sk">', false)->assertSee('Program dňa')
        ->assertSee('2. 2. 2027 (utorok) od 9:00')->assertSee('2. ročník');
    $this->get('/prihlasenie')->assertOk()->assertSee('Registračný formulár');
});

it('shows English dates and edition and links within the English site', function () {
    $this->get('/en')
        ->assertSee('Tuesday, 2 February 2027, from 9:00')
        ->assertSee('2nd edition')
        ->assertSee('href="' . url('/en/program') . '"', false)
        ->assertSee('href="' . url('/en/registration') . '"', false)
        ->assertDontSee('href="' . url('/program') . '"', false);
});

it('switches to the same page in the other language and announces both versions', function () {
    $this->get('/en/program')
        ->assertSee('<link rel="alternate" hreflang="sk" href="' . url('/program') . '">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="' . url('/en/program') . '">', false)
        ->assertSee('href="' . url('/program') . '" hreflang="sk"', false);

    $this->get('/vybor')->assertSee('href="' . url('/en/committee') . '" hreflang="en"', false);
});

it('highlights the current page in the English navigation', function () {
    $html = $this->get('/en/question')->getContent();

    expect(substr_count($html, 'aria-current="page"'))->toBe(1)
        ->and($html)->toMatch('#<a href="' . preg_quote(url('/en/question'), '#') . '"\s+aria-current="page"#');
});

it('registers in English, mails the participant in English and the organisers in Slovak', function () {
    Mail::fake();

    $this->post('/en/registration', [
        'name' => 'Jane Doe', 'email' => 'jane@example.com', 'institution' => 'MIT',
        'participation_type' => 'presentation', 'title' => 'AI tutors', 'abstract' => 'Short abstract.', 'block' => 'teaching',
    ])->assertRedirect(url('/en/registration') . '#registration-form')
        ->assertSessionHas('success', 'Thank you for registering! We have sent you a confirmation e-mail.');

    $reg = Registration::where('email', 'jane@example.com')->firstOrFail();
    expect($reg->locale)->toBe('en');

    Mail::assertSent(RegistrationConfirmation::class, function ($mail) {
        $mail->assertHasSubject('Registration confirmed – Conference on AI in Education 2027');
        $mail->assertSeeInHtml('Registration confirmed');
        $mail->assertSeeInHtml('Tuesday, 2 February 2027, from 9:00');
        $mail->assertSeeInHtml('Block 1 – AI in teaching and research');
        $mail->assertSeeInHtml(url('/en/program'), false);
        $mail->assertSeeInText('The programme committee will now review your talk');

        return $mail->hasTo('jane@example.com');
    });
    Mail::assertSent(NewRegistrationNotification::class, function ($mail) {
        $mail->assertSeeInHtml('Nová registrácia');
        $mail->assertSeeInHtml('2. 2. 2027 (utorok) od 9:00');

        return $mail->hasTo('konferenciaAI@fri.uniza.sk');
    });
});

it('stores Slovak as the language of Slovak registrations', function () {
    Mail::fake();
    $this->post('/prihlasenie', ['name' => 'Ján', 'email' => 'jan@example.com', 'institution' => 'UNIZA', 'participation_type' => 'passive']);

    expect(Registration::where('email', 'jan@example.com')->value('locale'))->toBe('sk');
    Mail::assertSent(RegistrationConfirmation::class, function ($mail) {
        $mail->assertHasSubject('Potvrdenie registrácie – Konferencia o AI vo vzdelávaní 2027');
        $mail->assertSeeInHtml('Registrácia potvrdená');

        return true;
    });
});

it('shows validation errors in English', function () {
    $this->post('/en/registration', ['participation_type' => 'presentation'])
        ->assertSessionHasErrors([
            'name' => 'Full name is required',
            'title' => 'The title of the talk is required for active participation',
        ]);
});

it('sends review mails in the language of the registration', function () {
    Mail::fake();
    $admin = User::factory()->create(['is_admin' => true]);
    $en = Registration::create([
        'name' => 'Jane Doe', 'email' => 'jane@example.com', 'institution' => 'MIT', 'locale' => 'en',
        'participation_type' => 'presentation', 'title' => 'AI tutors', 'review_status' => Registration::REVIEW_PENDING,
    ]);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee('Registrácia v angličtine');
    $this->get(route('admin.registration.show', $en))
        ->assertSee('angličtina')
        ->assertSee('Zdôvodnenie napíšte po anglicky');

    $this->post(route('admin.registration.approve', $en));
    Mail::assertSent(TalkApproved::class, function ($mail) {
        $mail->assertHasSubject('Review of your talk – Conference on AI in Education 2027');
        $mail->assertSeeInHtml('Talk accepted');

        return true;
    });

    $this->post(route('admin.registration.reject', $en), ['review_comment' => 'The topic overlaps with an invited talk.']);
    Mail::assertSent(TalkRejected::class, function ($mail) {
        $mail->assertSeeInHtml('Talk not accepted');
        $mail->assertSeeInHtml('The topic overlaps with an invited talk.');

        return true;
    });
});

it('keeps the admin in Slovak', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get(route('admin.conferences.index'))
        ->assertSee('2. 2. 2027 (utorok) od 9:00')
        ->assertDontSee('Tuesday');
    expect(Route::has('en.admin.dashboard'))->toBeFalse();
});

it('keeps visitors in English through the recordings login', function () {
    $this->get('/en/recordings/list')->assertRedirect(url('/en/recordings'));

    $this->post('/en/recordings', ['password' => 'wrong'])->assertSessionHasErrors(['password' => 'Incorrect password.']);
    $this->post('/en/recordings', ['password' => 'test-recordings-password'])->assertRedirect(url('/en/recordings/list'));
    $this->get('/en/recordings')->assertRedirect(url('/en/recordings/list'));
});
