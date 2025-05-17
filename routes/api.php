<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::apiResource('albums', AlbumController::class);

Route::apiResource('songs', SongController::class);
