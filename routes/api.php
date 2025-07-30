<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register'])->middleware('web');
Route::post('/login', [AuthController::class, 'login'])->middleware('web');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'emailVerify'])->name('verification.verify');
Route::post('/resend-email-verify', [AuthController::class, 'resendEmailVerificationMail'])->middleware('auth:sanctum');

Route::apiResource('profile', ProfileController::class);
