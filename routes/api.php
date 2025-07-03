<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public business listings
Route::get('/businesses', [BusinessController::class, 'index']);
Route::get('/businesses/{business}', [BusinessController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Public deal information
Route::get('/deals/recent', [DealController::class, 'recent']);
Route::get('/deals/highest', [DealController::class, 'highest']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'recentActivity']);

    // Business management (for owners)
    Route::middleware(['auth', 'owner'])->group(function () {
        Route::post('/businesses', [BusinessController::class, 'store']);
        Route::put('/businesses/{business}', [BusinessController::class, 'update']);
        Route::delete('/businesses/{business}', [BusinessController::class, 'destroy']);
        Route::get('/my-businesses', [BusinessController::class, 'myBusinesses']);

        // Offer management for business owners
        Route::get('/businesses/{business}/offers', [OfferController::class, 'businessOffers']);
        Route::post('/offers/{offer}/accept', [OfferController::class, 'acceptOffer']);
        Route::post('/offers/{offer}/counter', [OfferController::class, 'counterOffer']);
        Route::post('/offers/{offer}/reject', [OfferController::class, 'rejectOffer']);
    });

    // Investor routes
    Route::middleware(['auth', 'investor'])->group(function () {
        // Making offers
        Route::post('/offers', [OfferController::class, 'store']);
        Route::get('/my-offers', [OfferController::class, 'myOffers']);

        // Wishlist management
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{business}', [WishlistController::class, 'destroy']);
        Route::get('/wishlist/check/{business}', [WishlistController::class, 'check']);
    });

    // Common routes for both user types
    Route::get('/offers/{offer}', [OfferController::class, 'show']);
    Route::get('/deals', [DealController::class, 'index']);
    Route::get('/deals/{deal}', [DealController::class, 'show']);
});
