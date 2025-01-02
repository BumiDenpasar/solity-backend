<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\NotesController;
use Illuminate\Support\Facades\Route;
  
   
Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::middleware('auth:sanctum')->post('logout', 'logout');
});
         
Route::middleware('auth:sanctum')->group( function () {
    Route::resource('notes', NotesController::class);
    Route::get('user', action: [AuthController::class, 'getCurrentUser'])->name('getCurrentUser');
    Route::get(uri: 'verify-token', action: [AuthController::class, 'verifyToken'])->name('verifyToken');
    Route::put('profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});