<?php

uses()->group('app.invoice-download.show');

use App\Models\User;
use Illuminate\Support\Facades\URL;

afterEach(fn () => stripeSandboxFlush());

test('unsigned link is refused', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/invoices/in_123/download?user={$user->id}");

    $response->assertStatus(403);
});

test('signed link for a user without a stripe customer is not found', function () {
    $user = User::factory()->create();

    $url = URL::temporarySignedRoute('invoices.download', now()->addMinutes(5), [
        'invoice' => 'in_123',
        'user' => $user->id,
    ]);

    $response = $this->getJson($url);

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
});

test('signed link for an invoice that does not exist is not found', function () {
    $user = stripeSandboxUser();

    $url = URL::temporarySignedRoute('invoices.download', now()->addMinutes(5), [
        'invoice' => 'in_doesnotexist',
        'user' => $user->id,
    ]);

    $response = $this->getJson($url);

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
})->group('stripe');

test('signed link cannot download another customers invoice', function () {
    $user = stripeSandboxUser();
    $other = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($other);

    $url = URL::temporarySignedRoute('invoices.download', now()->addMinutes(5), [
        'invoice' => $invoice->id,
        'user' => $user->id,
    ]);

    $response = $this->getJson($url);

    $response->assertStatus(404)
        ->assertJsonPath('error', 'not_found');
})->group('stripe');

test('signed link downloads the invoice as a pdf without a token', function () {
    $user = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($user);

    $url = URL::temporarySignedRoute('invoices.download', now()->addMinutes(5), [
        'invoice' => $invoice->id,
        'user' => $user->id,
    ]);

    $response = $this->get($url);

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->headers->get('Content-Disposition'))
        ->toStartWith('attachment')
        ->toContain($invoice->date()->format('d_m_y') . '.pdf');
})->group('stripe');
