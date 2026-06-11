<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Repositories\Contracts\LeaveRepositoryInterface;

class LeaveRepository
implements LeaveRepositoryInterface
{
    public function create(
        array $data
    ) {
        return LeaveRequest::create(
            $data
        );
    }

    public function find(
        int $id
    ) {
        return LeaveRequest::findOrFail(
            $id
        );
    }

    public function employeeLeaves(
        int $employeeId,
        array $filters = []
    ) {

        return LeaveRequest::query()

            ->where(
                'employee_id',
                $employeeId
            )

            ->when(
                $filters['status'] ?? null,
                fn($q, $status)
                =>
                $q->where(
                    'status',
                    $status
                )
            )

            ->latest()

            ->paginate(10);
    }

    public function all(
        array $filters = []
    ) {

        return LeaveRequest::with(
            'employee'
        )

            ->when(
                $filters['status'] ?? null,
                fn($q, $status)
                =>
                $q->where(
                    'status',
                    $status
                )
            )

            ->latest()

            ->paginate(10);
    }
}
