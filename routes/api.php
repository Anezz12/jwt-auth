<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes

Route::prefix('auth')->group(function () {
    // Regular Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    // OAuth - Google
    Route::get('/google', [AuthController::class, 'redirectToProvider']);
    Route::post('/google/callback', [AuthController::class, 'exchangeGoogleCode']);

    // OAuth - Github
    Route::get('/github', [AuthController::class, 'redirectToGithub']);
    Route::post('/github/callback', [AuthController::class, 'exchangeGithubCode']);
});

// Protected Authentication Routes

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/unlink-provider', [AuthController::class, 'unlinkProvider']);
});

// API v1 - Public Routes

Route::prefix('v1')->group(function () {

    // Homepage
    Route::get('/homepage', [HomepageController::class, 'index'])
        ->name('api.homepage');

    // Articles
    Route::prefix('articles')->name('api.articles.')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])->name('index');
        Route::get('/trending', [ArticleController::class, 'trending'])->name('trending');
        Route::get('/featured', [ArticleController::class, 'featured'])->name('featured');
        Route::get('/{slug}', [ArticleController::class, 'show'])->name('show');
    });

    // Categories
    Route::prefix('categories')->name('api.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/{slug}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{slug}/articles', [CategoryController::class, 'articles'])->name('articles');
    });

    // Tags
    Route::prefix('tags')->name('api.tags.')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::get('/{slug}', [TagController::class, 'show'])->name('show');
        Route::get('/{slug}/articles', [TagController::class, 'articles'])->name('articles');
    });

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('api.search');
});

// API v1 - Protected Routes (Admin/Author)

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Articles Management
    Route::prefix('articles')->name('api.articles.')->group(function () {
        Route::post('/', [ArticleController::class, 'store'])->name('store');
        Route::put('/{id}', [ArticleController::class, 'update'])->name('update');
        Route::delete('/{id}', [ArticleController::class, 'destroy'])->name('destroy');
    });

    // Categories Management
    Route::prefix('categories')->name('api.categories.')->group(function () {
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Tags Management
    Route::prefix('tags')->name('api.tags.')->group(function () {
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::put('/{id}', [TagController::class, 'update'])->name('update');
        Route::delete('/{id}', [TagController::class, 'destroy'])->name('destroy');
    });
});
