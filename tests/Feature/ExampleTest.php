<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the public stylesheet URL changes with the file, so a deploy needs no reload', function () {
    $version = filemtime(public_path('css/conference.css'));

    $this->get('/')->assertSee('css/conference.css?v=' . $version, false);
    $this->get('/en')->assertSee('css/conference.css?v=' . $version, false);
});
