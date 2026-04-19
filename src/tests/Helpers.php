<?php

use App\Models\User;

if (!function_exists('actingAsUser')) {
    function actingAsUser(): User
    {
        $user = User::factory()->create();

        test()->actingAs($user);
        test()->withHeader('Accept', 'application/json');

        return $user;
    }
}