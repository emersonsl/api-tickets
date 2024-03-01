<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BatchController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\SectorController;
use App\Http\Controllers\Api\V1\TicketController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function(){
    Route::prefix('users')->group(function(){
        Route::get('/', [UserController::class, 'index']);
        Route::post('/register', [UserController::class, 'create']);
        Route::put('/promote', [UserController::class, 'promote'])->middleware(['auth:sanctum', 'can:promote users']);
    });
    Route::prefix('auth')->group(function(){
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
    });
    Route::prefix('event')->group(function(){
        Route::post('/create', [EventController::class, 'create'])->middleware(['auth:sanctum', 'can:create event']);
    });
    Route::prefix('sector')->group(function(){
        Route::post('/create', [SectorController::class, 'create'])->middleware(['auth:sanctum', 'can:create sector']);
    });
    Route::prefix('batch')->group(function(){
        Route::post('/create', [BatchController::class, 'create'])->middleware(['auth:sanctum', 'can:create batch']);
    });
    Route::prefix('coupon')->group(function(){
        Route::post('/create', [CouponController::class, 'create'])->middleware(['auth:sanctum', 'can:create coupon']);
    });
    Route::prefix('ticket')->group(function(){
        Route::post('/reserve', [TicketController::class, 'create'])->middleware(['auth:sanctum', 'can:reserve ticket']);
    });
    Route::prefix('payment')->group(function(){
        Route::post('/create', [PaymentController::class, 'create'])->middleware(['auth:sanctum', 'can:create payment']);
    });
});

Route::get('/test', [TestController::class, 'index']);
