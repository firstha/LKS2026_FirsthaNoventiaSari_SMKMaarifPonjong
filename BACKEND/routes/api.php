<?php

use App\Http\Controllers\Api\BusinessVerificationController;
use App\Http\Controllers\Api\FinancingApplicationController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
    Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
        return $request->user();
    });

    Route::middleware(['auth:sanctum','role:admin'])->get('/admin-only', function () {
        return response()->json(['message' => 'Admin only']);
    });

    Route::middleware(['auth:sanctum','role:applicant'])->group(function () {
    Route::post('/business-verifications', [BusinessVerificationController::class, 'store']);
    });

    Route::middleware(['auth:sanctum','role:verifier'])->group(function () {
    Route::patch('/business-verifications/{id}/approve', [BusinessVerificationController::class, 'approve']);
    });

    Route::middleware(['auth:sanctum','role:applicant'])->group(function () {
    Route::post('/financing-applications', [FinancingApplicationController::class, 'store']);
    });

});