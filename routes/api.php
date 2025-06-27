<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\UserMedicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public drug search (with rate limiting)
Route::get('/drugs/search', [DrugController::class, 'search']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User medications
    Route::get('/medications', [UserMedicationController::class, 'index']);
    Route::post('/medications', [UserMedicationController::class, 'store']);
    Route::delete('/medications', [UserMedicationController::class, 'destroy']);
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
