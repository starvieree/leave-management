<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use App\Models\LeaveHistory;
use App\Models\LeaveRequest;
use App\Services\QuotaService;
use App\Services\LeaveService;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Repositories\Contracts\LeaveRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LeaveRepositoryInterface $repository,
        private LeaveService $leaveService,
        private QuotaService $quotaService
    ) {}

    // Employee List Own Leave
    public function index(Request $request)
    {
        $leaves = $this->repository->employeeLeaves(
            Auth::id(),
            $request->all()
        );

        return LeaveRequestResource::collection(
            $leaves
        )->additional([
            'success' => true,
            'message' => 'Data loaded'
        ]);
    }

    // Employee Create Leave
    public function store(StoreLeaveRequest $request)
    {
        $days = $this->leaveService->calculateDays(
            $request->start_date,
            $request->end_date
        );

        $this->quotaService->validateQuota(
            Auth::id(),
            $days
        );

        $this->leaveService->checkOverlap(
            Auth::id(),
            $request->start_date,
            $request->end_date
        );

        return DB::transaction(
            function () use ($request, $days) {
                $path = $request->file('attachment')->store(
                    'leave-attachments',
                );

                $leave = LeaveRequest::create([
                    'employee_id' => Auth::id(),
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_days' => $days,
                    'reason' => $request->reason,
                    'attachment' => $path
                ]);

                LeaveHistory::create([
                    'leave_request_id' => $leave->id,
                    'created_by' => Auth::id(),
                    'action' => 'SUBMIT',
                    'description' => 'Employee submitted leave'
                ]);

                return $this->success(
                    new LeaveRequestResource($leave),
                    'Leave submitted',
                    201
                );
            }
        );
    }

    public function attachment(int $id)
    {
        $leave = LeaveRequest::findOrFail($id);

        Gate::authorize('view', $leave);

        return Storage::download(
            $leave->attachment
        );
    }
}
