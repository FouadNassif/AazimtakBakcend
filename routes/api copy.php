<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ImageUploadController;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signup']);
Route::get('/check-auth', [UserController::class, 'checkAuthByToken']);

// Client Dashboard
Route::post('/dashboard', [UserController::class, 'showDashboard']);
Route::post('/test', [UserController::class, 'test']);

// Guest Routes
Route::post('/dashboard/guests', [UserController::class, 'allGuestsDashboard']);
Route::post('/dashboard/guests/add', [UserController::class, 'addGuestDashboard']);
Route::post('/dashboard/guests/edit', [UserController::class, 'editGuest']);
Route::post('/dashboard/guests/delete', [UserController::class, 'deleteGuest']);

// Wedding Routes
Route::post('/dashboard/wedding/getData', [UserController::class, 'getWeddingData']);
Route::post('/dashboard/wedding/saveData', [UserController::class, 'saveWeddingData']);

Route::post('/dashboard/account/edit', [UserController::class, 'editAccount']);
// Images Handling
Route::post('/dashboard/wedding/upload-images', [ImageUploadController::class, 'uploadImages']);
Route::post('/dashboard/wedding/getAllUserImages', [ImageUploadController::class, 'getAllUserImages']);

// Check Wedding Link Data
Route::post('/wedding/check/weddingId', [ImageUploadController::class, 'checkWeddingId']);
Route::post('/wedding/check/groomAndBride', [ImageUploadController::class, 'checkGroomAndBride']);
Route::post('/wedding/check/guestName', [ImageUploadController::class, 'checkGuestName']);

// Show wedding Card 
Route::post('/wedding/showWeddingCard', [UserController::class, 'getCardWeddingDetails']);
Route::post('/wedding/setAttendance', [UserController::class, 'setAttendance']);

// Admin Routes
Route::post('/admin/dashboard', [AdminController::class, 'adminDashboard']);
Route::post('/admin/dashboard/wedding/addwedding', [AdminController::class, 'addWedding']);
Route::post('/admin/dashboard/wedding/getAll', [AdminController::class, 'getAllWeddings']);
