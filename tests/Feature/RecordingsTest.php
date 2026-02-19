<?php

namespace Tests\Feature;

use Tests\TestCase;

class RecordingsTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_recordings()
    {
        // Pokús sa pristúpiť na stiahnutie bez hesla
        $response = $this->get('/zaznamy/presentation/test.pdf');

        // Mal by byť presmerovaný na prihlasovaciu stránku
        $this->assertRedirect(route('recordings.login'));
    }

    public function test_authenticated_user_can_access_recordings_list()
    {
        // Prihlás sa
        $this->post('/zaznamy', ['password' => 'konferenciaai']);

        // Teraz by mal vidieť zoznam
        $response = $this->get('/zaznamy/list');
        $response->assertOk();
    }
}

