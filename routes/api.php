<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;

// User Auth Routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signup']);
Route::get('/check-auth', [UserController::class, 'checkAuthByToken']);