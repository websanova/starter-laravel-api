<?php

uses()->group('app.settings.show');

test('guest can retrieve settings', function () {
    $response = $this->getJson('/settings');

    $response->assertStatus(200)
        ->assertExactJson(['data' => []]);
});

test('settings are available on the admin path', function () {
    $response = $this->getJson('/admin/settings');

    $response->assertStatus(200)
        ->assertExactJson(['data' => []]);
});
