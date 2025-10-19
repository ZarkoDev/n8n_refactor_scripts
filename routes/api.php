<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\V1\AdScriptTaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication API routes
Route::prefix('auth')->group(function (): void {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
});

// SignedIn API routes
Route::middleware('auth:sanctum')->group(function (): void {
    // Ad Script Tasks API (v1) routes
    Route::prefix('ad-scripts')->group(function (): void {
        Route::resource('/', AdScriptTaskController::class);
    });
});


Route::prefix('ad-scripts')->group(function (): void {
    // Webhooks
    Route::middleware('n8n.callback')->group(function (): void {
        Route::post('{id}/result', [AdScriptTaskController::class, 'result']);
        Route::post('{id}/failed', [AdScriptTaskController::class, 'failed']);
    });
});

