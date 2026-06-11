<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveHistory;
use App\Models\LeaveQuota;
use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRejectedNotification;
use App\Repositories\Contracts\LeaveRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminLeaveController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LeaveRepositoryInterface $repository
    ) {}

    // Admin List All Leave
    public function index(Request $request)
    {
        $leaves = $this->repository->all(
            $request->all()
        );

        return $this->success(
            LeaveRequestResource::collection($leaves)
        );
    }

    // Approve Leave
    public function approve(
        int $id
    ) {
        return DB::transaction(
            function () use ($id) {
                $leave = $this->repository->find($id);

                if ($leave->status !== 'pending') {
                    return $this->error(
                        'Already processed',
                        422
                    );
                }

                $quota = LeaveQuota::where(
                    'user_id',
                    $leave->employee_id
                )->where(
                    'year',
                    now()->year
                )->firstOrFail();

                $remaining = $quota->quota - $quota->used;

                if ($remaining < $leave->total_days)
                {
                    return $this->error(
                        'Insufficient quota',
                        422
                    );
                }

                $quota->increment(
                    'used',
                    $leave->total_days
                );

                $leave->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                LeaveHistory::create([
                    'leave_request_id' => $leave->id,
                    'created_by' => Auth::id(),
                    'action' => 'APPROVE',
                    'description' => 'Admin approved leave'
                ]);

                // $leave->employee->notify(
                //     new LeaveApprovedNotification()
                // );

                return $this->success(
                    null,
                    'Leave approved'
                );
            }
        );
    }

    // Reject Leave
    public function reject(
        RejectLeaveRequest $request,
        int $id
    ) {
        return DB::transaction(
            function () use ($request, $id) {

                $leave = $this->repository->find($id);

                if ($leave->status !== 'pending') {
                    return $this->error(
                        'Already processed',
                        422
                    );
                }

                $leave->update([
                    'status' => 'rejected',
                    'rejected_reason' => $request->reason
                ]);

                LeaveHistory::create([
                    'leave_request_id' => $leave->id,
                    'created_by' => Auth::id(),
                    'action' => 'REJECT',
                    'description' => 'Admin rejected leave'
                ]);

                // $leave->employee->notify(
                //     new LeaveRejectedNotification()
                // );

                return $this->success(
                    null,
                    'Leave rejected'
                );
            }
        );
    }
}
