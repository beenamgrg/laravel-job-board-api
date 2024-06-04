<?php

use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request)
{
    return $request->user();
})->middleware('auth:api');

Route::get('/home', [UserController::class, 'index']);
Route::post('/login', [SessionController::class, 'postLogin']);
Route::middleware('auth:api')->group(function ()
{
    Route::post('/logout', [SessionController::class, 'logout']);
});
