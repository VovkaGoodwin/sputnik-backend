<?php

use App\Http\Controllers\AuthController;

Route::prefix('auth')
  ->controller(AuthController::class)
  ->name('auth.')
  ->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth:api');
    Route::get('/refresh', 'refresh')->name('refresh')->middleware('auth:api');
  });
