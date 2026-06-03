<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VocabularyController;
use Illuminate\Support\Facades\Route;

// Rate limit: 300 requests per 15 minutes
Route::middleware('throttle:300,15')->group(function () {

    // --- Auth ---
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // --- Collection (public reads) ---
    Route::get('/collection/stats', [CollectionController::class, 'stats']);
    Route::get('/collection/{id}', [CollectionController::class, 'show']);
    Route::get('/collection', [CollectionController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);

    // --- Collection (admin writes) ---
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/collection', [CollectionController::class, 'store']);
        Route::put('/collection/{id}', [CollectionController::class, 'update']);
        Route::delete('/collection/{id}', [CollectionController::class, 'destroy']);
    });

    // --- Vocabulary (public reads) ---
    Route::get('/vocabulary/stats', [VocabularyController::class, 'stats']);
    Route::get('/vocabulary/review-queue', [VocabularyController::class, 'reviewQueue']);
    Route::get('/vocabulary/lookup', [VocabularyController::class, 'lookup']);
    Route::get('/vocabulary', [VocabularyController::class, 'index']);

    // --- Vocabulary (admin writes) ---
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/vocabulary', [VocabularyController::class, 'store']);
        Route::put('/vocabulary/{id}/review', [VocabularyController::class, 'review']);
        Route::put('/vocabulary/{id}', [VocabularyController::class, 'update']);
        Route::delete('/vocabulary/{id}', [VocabularyController::class, 'destroy']);
    });
});
