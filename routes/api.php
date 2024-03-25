<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BatchController;
use App\Http\Controllers\Api\V1\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\SectorController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\WebHookController;

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

Route::prefix('v1')->group(function(){
    Route::prefix('users')->group(function(){
        Route::post('/register', [UserController::class, 'create']);
        Route::put('/promote', [UserController::class, 'promote'])->middleware(['auth:sanctum', 'can:promote users']);
    });
    Route::prefix('auth')->group(function(){
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
    });
    Route::prefix('event')->group(function(){
        Route::get('/', [EventController::class, 'index'])->middleware(['auth:sanctum', 'can:list events']);
        Route::post('/create', [EventController::class, 'create'])->middleware(['auth:sanctum', 'can:create event']);
        Route::put('/update/{event}', [EventController::class, 'update'])->middleware(['auth:sanctum', 'can:update event']);
        Route::delete('/delete/{event}', [EventController::class, 'destroy'])->middleware(['auth:sanctum', 'can:delete event']);
        Route::post('/uploadbanner', [EventController::class, 'uploadBanner'])->middleware(['auth:sanctum', 'can:upload banner event']);
        Route::get('/upcoming', [EventController::class, 'listUpcoming']);
        Route::get('/available', [EventController::class, 'listAvailable']);
    });
    Route::prefix('sector')->group(function(){
        Route::get('/', [SectorController::class, 'index'])->middleware(['auth:sanctum', 'can:list sectors']);
        Route::post('/create', [SectorController::class, 'create'])->middleware(['auth:sanctum', 'can:create sector']);
        Route::put('/update/{sector}', [SectorController::class, 'update'])->middleware(['auth:sanctum', 'can:update sector']);
        Route::delete('/delete/{sector}', [SectorController::class, 'destroy'])->middleware(['auth:sanctum', 'can:delete sector']);
    });
    Route::prefix('batch')->group(function(){
        Route::get('/', [BatchController::class, 'index'])->middleware(['auth:sanctum', 'can:list batches']);
        Route::post('/create', [BatchController::class, 'create'])->middleware(['auth:sanctum', 'can:create batch']);
        Route::delete('/delete/{batch}', [BatchController::class, 'destroy'])->middleware(['auth:sanctum', 'can:delete batch']);
    });
    Route::prefix('coupon')->group(function(){
        Route::get('/', [CouponController::class, 'index'])->middleware(['auth:sanctum', 'can:list coupons']);
        Route::post('/create', [CouponController::class, 'create'])->middleware(['auth:sanctum', 'can:create coupon']);
        Route::delete('/delete/{coupon}', [CouponController::class, 'destroy'])->middleware(['auth:sanctum', 'can:delete coupon']);
    });
    Route::prefix('ticket')->group(function(){
        Route::get('/', [TicketController::class, 'index'])->middleware(['auth:sanctum', 'can:list tickets']);
        Route::post('/reserve', [TicketController::class, 'create'])->middleware(['auth:sanctum', 'can:reserve ticket']);
    });
    Route::prefix('payment')->group(function(){
        Route::post('/create', [PaymentController::class, 'create'])->middleware(['auth:sanctum', 'can:create payment']);
    });
    Route::prefix('webhook')->group(function(){
        Route::post('/', [WebHookController::class, 'create']);
    });
});
