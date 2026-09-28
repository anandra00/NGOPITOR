<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CoffeeShopController;
use App\Http\Controllers\Api\V1\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        });
    });

    Route::prefix('coffee-shops')->group(function () {
        Route::get('/recommendations', RecommendationController::class)->name('api.v1.coffee-shops.recommendations');
        Route::get('/', [CoffeeShopController::class, 'index'])->name('api.v1.coffee-shops.index');
        Route::get('/{coffeeShop:slug}', [CoffeeShopController::class, 'show'])->name('api.v1.coffee-shops.show');
    });
});
