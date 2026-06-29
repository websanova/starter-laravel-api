<?php

uses()->group('app.login.throttle');

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);
});

test('login is throttled after too many attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ])->assertStatus(422);
    }

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(429)
        ->assertJsonPath('message', fn ($message) => str_contains($message, 'Too many requests'));
});

test('throttle resets after decay period', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ])->assertStatus(429);

    $this->travel(1)->minutes();

    $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ])->assertStatus(200);
});


