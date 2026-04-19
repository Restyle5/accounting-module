<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\TrialBalanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });

    // GL Journal routes will go here
    Route::apiResource('accounts', AccountController::class);
    // Accounting uses store/create to update changes rather than deleting or updating records.
    Route::apiResource('journal-entries', JournalEntryController::class)->except(["destroy", "update"]);

        // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/trial-balance', [TrialBalanceController::class, 'index']);
    });

});