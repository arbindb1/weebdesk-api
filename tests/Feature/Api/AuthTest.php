<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a user and returns a token', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Light',
        'email' => 'light@example.com',
        'password' => 'password-123!',
        'password_confirmation' => 'password-123!',
    ])
        ->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
        ->assertJsonMissingPath('user.password');

    expect(User::where('email', 'light@example.com')->exists())->toBeTrue();
});

it('validates registration input', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', ['email' => 'taken@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-pass-1']);

    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'secret-pass-1'])
        ->assertOk()
        ->assertJsonStructure(['user' => ['id'], 'token']);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('logs out by revoking the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});

it('requires authentication to log out', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});
