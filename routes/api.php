<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\AdminLeaveController;

// Public
Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::get('/auth/google/redirect', [AuthController::class,'redirectGoogle']);
Route::get('/auth/google/callback', [AuthController::class,'googleCallback']);

// Authenticated
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class,'logout']);
});

// Employee
Route::middleware(['auth:sanctum', 'employee'])->group(function(){
    Route::get('/leave-requests', [LeaveController::class,'index']);
    Route::post('/leave-requests', [LeaveController::class,'store']);
});

// Admin
Route::middleware(['auth:sanctum', 'admin'])->group(function(){
    Route::get('/admin/leave-requests', [AdminLeaveController::class,'index']);
    Route::patch('/admin/leave-requests/{id}/approve', [AdminLeaveController::class,'approve']);
    Route::patch('/admin/leave-requests/{id}/reject', [AdminLeaveController::class,'reject']);
});