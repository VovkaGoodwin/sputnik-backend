<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use \Orion\Facades\Orion;

Route::group(['as' => 'api.'], function () {
  Orion::resource('users', UserController::class)
    ->only(['store'])
    ->withoutMiddleware(['api']);
});
