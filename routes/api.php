<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\EventController;

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
});

Route::get('/test', [TestController::class, 'index']);
