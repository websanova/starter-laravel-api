<?php

uses()->group('admin.login.store');

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
    ]);
    $user->assignRole(UserRole::Admin);

    $response = $this->postJson('/admin/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'],
            'token',
        ]);
});

test('super can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'super@example.com',
        'password' => Hash::make('password123'),
    ]);
    $user->assignRole(UserRole::Super);

    $response = $this->postJson('/admin/login', [
        'email' => 'super@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'token']);
});

test('regular user cannot login via admin endpoint', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/admin/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login fails with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
    ]);
    $user->assignRole(UserRole::Admin);

    $response = $this->postJson('/admin/login', [
        'email' => 'admin@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login fails with missing fields', function () {
    $response = $this->postJson('/admin/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

