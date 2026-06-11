<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LeaveQuota;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Laravel\Socialite\Facades\Socialite;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        LeaveQuota::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'quota' => 12,
            'used' => 0
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(LoginRequest $request)
    {
        if (
            !Auth::attempt(
                $request->only(
                    'email',
                    'password'
                )
            )
        ) {

            return $this->error(
                'Invalid credentials',
                401
            );
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(
            null,
            'Logout success'
        );
    }

    public function redirectGoogle()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');
        
        return $driver->stateless()->redirect();
    }

    public function googleCallback()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');

        $googleUser = $driver->stateless()->user();

        $user = User::firstOrCreate(
            [
                'email' => $googleUser->email
            ],
            [
                'name' => $googleUser->name,
                'provider' => 'google',
                'provider_id' => $googleUser->id,
                'role' => 'employee'
            ]
        );

        LeaveQuota::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => now()->year
            ],
            [
                'quota' => 12,
                'used' => 0
            ]
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token
        ]);
    }
}
