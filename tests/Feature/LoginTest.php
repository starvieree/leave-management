<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_user_can_login()
    {
        $user =
            User::factory()->create([
                'password' =>
                bcrypt('password')
            ]);

        $response =
            $this->postJson(
                '/api/login',
                [
                    'email' => $user->email,
                    'password' => 'password'
                ]
            );
        $response
            ->assertStatus(200);
    }
}
