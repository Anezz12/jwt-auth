<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Regular Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// OAuth Routes Google
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);
Route::post('/auth/google/exchange', [AuthController::class, 'exchangeGoogleCode']);

// OAuth Routes Github
Route::get('/auth/github', [AuthController::class, 'redirectToProvider']);
Route::post('/auth/github/exchange', [AuthController::class, 'exchangeGithubCode']);

// Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/unlink-provider', [AuthController::class, 'unlinkProvider']);
});
