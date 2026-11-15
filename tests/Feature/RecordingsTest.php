<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_recordings()
    {
        // Pokús sa pristúpiť na stiahnutie bez hesla
        $response = $this->get('/zaznamy/presentation/test.pdf');

        // Mal by byť presmerovaný na prihlasovaciu stránku
        $response->assertRedirect(route('recordings.login'));
    }

    public function test_authenticated_user_can_access_recordings_list()
    {
        // Prihlás sa
        $this->post('/zaznamy', ['password' => 'test-recordings-password']);

        // Teraz by mal vidieť zoznam
        $response = $this->get('/zaznamy/list');
        $response->assertOk();
    }

    public function test_wrong_password_is_rejected()
    {
        $this->post('/zaznamy', ['password' => 'konferenciaai'])->assertSessionHasErrors('password');
        $this->get('/zaznamy/list')->assertRedirect(route('recordings.login'));
    }

    public function test_login_is_disabled_when_no_password_is_configured()
    {
        config(['services.recordings.password' => null]);

        $this->post('/zaznamy', ['password' => ''])->assertSessionHasErrors('password');
    }
}
