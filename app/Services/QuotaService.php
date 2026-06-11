<?php

namespace App\Services;

use App\Models\LeaveQuota;
use App\Exceptions\LeaveQuotaExceededException;

class QuotaService
{
    public function currentQuota(
        int $userId
    ) {
        return LeaveQuota::firstOrCreate(

            [
                'user_id' => $userId,
                'year' => now()->year
            ],

            [
                'quota' => 12,
                'used' => 0
            ]
        );
    }

    public function validateQuota(
        int $userId,
        int $days
    ) {
        $quota =
            $this->currentQuota(
                $userId
            );

        $remaining =
            $quota->quota -
            $quota->used;

        if (
            $remaining < $days
        ) {

            throw new LeaveQuotaExceededException(
                'Leave quota exceeded'
            );
        }
    }
}
