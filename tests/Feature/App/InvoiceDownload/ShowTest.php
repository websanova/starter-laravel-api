<?php

uses()->group('app.invoice-download.show');

use App\Models\User;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot download an invoice', function () {
    $response = $this->getJson('/invoices/in_123/download');

    $response->assertStatus(401);
});

test('user without a stripe customer cannot download an invoice', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/invoices/in_123/download');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
});

test('user cannot download an invoice that does not exist', function () {
    $user = stripeSandboxUser();

    $response = $this->actingAs($user)->getJson('/invoices/in_doesnotexist/download');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
})->group('stripe');

test('user cannot download another customers invoice', function () {
    $user = stripeSandboxUser();
    $other = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($other);

    $response = $this->actingAs($user)->getJson("/invoices/{$invoice->id}/download");

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
})->group('stripe');

test('user can download their invoice as a pdf', function () {
    $user = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($user);

    $response = $this->actingAs($user)->get("/invoices/{$invoice->id}/download");

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->headers->get('Content-Disposition'))->toStartWith('attachment');
})->group('stripe');
