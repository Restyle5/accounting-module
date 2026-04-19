<?php

use App\Models\User;

it('returns a token on valid login', function () {
    User::factory()->create([
        'email'    => 'admin@gl.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'admin@gl.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'user']);
});

it('returns 401 on invalid credentials', function () {
    User::factory()->create([
        'email'    => 'admin@gl.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'admin@gl.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials.']);
});

it('returns validation error when email is missing', function () {
    $response = $this->postJson('/api/auth/login', [
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns validation error when password is missing', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@gl.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns authenticated user on me endpoint', function () {
    actingAsUser();

    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure(['id', 'name', 'email']);
});

it('returns 401 on me endpoint without token', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});

it('logs out and revokes token', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully.']);
});

it('cannot access protected route after logout', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $headers = ['Authorization' => 'Bearer ' . $token];

    $this->withHeaders($headers)->postJson('/api/auth/logout');

    // Clear any cached auth state
    auth()->forgetGuards();

    $response = $this->withHeaders($headers)->getJson('/api/auth/me');

    $response->assertStatus(401);
});
