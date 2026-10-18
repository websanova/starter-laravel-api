<?php

uses()->group('app.invoice.index');

use App\Models\User;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot list invoices', function () {
    $response = $this->getJson('/invoices');

    $response->assertStatus(401);
});

test('user without a stripe customer gets an empty page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/invoices');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.total', 0);
});

test('per page above the limit returns validation error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/invoices?per_page=101');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('per_page');
});

test('user can list their invoices', function () {
    $user = stripeSandboxUser();

    $invoice = stripeSandboxInvoice($user, 2500);

    $response = $this->actingAs($user)->getJson('/invoices');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data' => [['amount', 'currency', 'date', 'id', 'number', 'status']],
            'meta' => ['current_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('data.0.id', $invoice->id)
        ->assertJsonPath('data.0.amount', 2500)
        ->assertJsonPath('data.0.status', 'paid')
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.total', 1);
})->group('stripe');

test('invoices are paged locally', function () {
    $user = stripeSandboxUser();

    $first = stripeSandboxInvoice($user);
    $second = stripeSandboxInvoice($user);

    $pageOne = $this->actingAs($user)->getJson('/invoices?per_page=1&page=1');
    $pageTwo = $this->actingAs($user)->getJson('/invoices?per_page=1&page=2');

    $pageOne->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.total', 2);

    $pageTwo->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.total', 2);

    expect([$pageOne->json('data.0.id'), $pageTwo->json('data.0.id')])
        ->toEqualCanonicalizing([$first->id, $second->id]);
})->group('stripe');
