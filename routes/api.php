<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'store']);

Route::post('/authentications', [AuthController::class, 'store']);
Route::put('/authentications', [AuthController::class, 'update']);
Route::delete('/authentications', [AuthController::class, 'destroy']);

Route::apiResource('/albums', AlbumController::class);

Route::apiResource('/songs', SongController::class);
