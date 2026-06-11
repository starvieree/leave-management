<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function view(
        User $user,
        LeaveRequest $leave
    ): bool {
        return
            $user->id ===
            $leave->employee_id;
    }
}
