<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminCheck;

Route::get('/user', function (Request $request)
{
    return $request->user();
})->middleware('auth:api');

Route::get('/home', [UserController::class, 'index']);
Route::post('/login', [SessionController::class, 'postLogin']);
Route::post('/sign-up', [SessionController::class, 'store']);
Route::middleware('auth:api')->group(function ()
{
    Route::post('/logout', [SessionController::class, 'logout']);
    Route::get('/jobs', [SessionController::class, 'index']);

    Route::middleware([AdminCheck::class])->group(function ()
    {
        Route::post('/job-store', [JobController::class, 'store']);
        Route::post('/company-store', [CompanyController::class, 'store']);
    });
});
