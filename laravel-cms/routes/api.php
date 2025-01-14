<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::group(['middleware' => 'auth:api'], function() {
    Route::post('user', [AuthController::class, 'user']);
});

Route::middleware('auth:api')->get('/auth/check-token', function () {
    return response()->json(['is_valid' => true]);
});
