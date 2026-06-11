<?php

namespace Tests\Feature;

use App\Models\LeaveQuota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile; 
use Tests\TestCase;

class CreateLeaveTest extends TestCase
{
    use RefreshDatabase; 

    public function test_employee_can_create_leave()
    {
        Storage::fake();

        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        LeaveQuota::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'quota' => 12,
            'used' => 0
        ]);

        $response = $this->actingAs(
            $user, 'sanctum'
        )->post(
            '/api/leave-requests',
            [
                'start_date' => now()
                    ->addDay()
                    ->format('Y-m-d'),

                'end_date' => now()
                    ->addDays(2)
                    ->format('Y-m-d'),
                'reason' => 'Family event',
                'attachment' => UploadedFile::fake()->image('test.jpg')
            ]
        );
        $response
            ->assertStatus(201);
    }
}
