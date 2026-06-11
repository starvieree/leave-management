<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\LeaveRequest;
use App\Exceptions\LeaveOverlapException;

class LeaveService
{
    public function calculateDays(
        string $startDate,
        string $endDate
    ) {
        return Carbon::parse(
            $startDate
        )->diffInDays(
            Carbon::parse(
                $endDate
            )
        ) + 1;
    }

    public function checkOverlap(
        int $userId,
        string $startDate,
        string $endDate
    ) {
        $exists = LeaveRequest::query()->where(
            'employee_id',
            $userId
        )->whereIn(
            'status',
            ['pending', 'approved']
        )->where(function ($query) use ($startDate, $endDate) {
            $query->where(
                'start_date',
                '<=',
                $endDate
            )->where(
                'end_date',
                '>=',
                $startDate
            );
        })->exists();

        if ($exists) {
            throw new LeaveOverlapException(
                'Leave request overlaps existing leave'
            );
        }
    }
}
