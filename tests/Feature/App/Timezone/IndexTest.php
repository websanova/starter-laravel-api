<?php

uses()->group('app.timezone.index');

test('guest can retrieve timezones', function () {
    $response = $this->getJson('/timezones');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);

    expect($response->json('data'))
        ->toBeArray()
        ->not->toBeEmpty()
        ->toContain('America/New_York');
});

test('timezones are available on the admin path', function () {
    $response = $this->getJson('/admin/timezones');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);

    expect($response->json('data'))->toContain('America/New_York');
});
