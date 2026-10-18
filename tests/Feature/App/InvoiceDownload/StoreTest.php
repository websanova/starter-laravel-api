<?php

uses()->group('app.invoice-download.store');

use App\Models\User;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot request a download link', function () {
    $response = $this->postJson('/invoices/in_123/download');

    $response->assertStatus(401);
});

test('user without a stripe customer cannot request a download link', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/invoices/in_123/download');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
});

test('user cannot request a link for an invoice that does not exist', function () {
    $user = stripeSandboxUser();

    $response = $this->actingAs($user)->postJson('/invoices/in_doesnotexist/download');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
})->group('stripe');

test('user cannot request a link for another customers invoice', function () {
    $user = stripeSandboxUser();
    $other = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($other);

    $response = $this->actingAs($user)->postJson("/invoices/{$invoice->id}/download");

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
})->group('stripe');

test('user can request a signed download link for their invoice', function () {
    $user = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($user);

    $response = $this->actingAs($user)->postJson("/invoices/{$invoice->id}/download");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['url']]);

    expect($response->json('data.url'))
        ->toContain("/invoices/{$invoice->id}/download")
        ->toContain('signature=');
})->group('stripe');
