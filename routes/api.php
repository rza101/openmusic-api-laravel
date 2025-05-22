<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlaylistCollaborationController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistSongController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserController;
use App\Models\PlaylistCollaboration;
use Illuminate\Support\Facades\Route;

Route::post('/users', [UserController::class, 'store']);

Route::post('/authentications', [AuthController::class, 'store']);
Route::put('/authentications', [AuthController::class, 'update']);
Route::delete('/authentications', [AuthController::class, 'destroy']);

Route::apiResource('/albums', AlbumController::class);

Route::apiResource('/songs', SongController::class);

Route::middleware('auth.jwt')->group(function () {
    Route::post('/playlists', [PlaylistController::class, 'store']);
    Route::get('/playlists', [PlaylistController::class, 'index']);
    Route::delete('/playlists/{id}', [PlaylistController::class, 'destroy']);

    Route::post('/playlists/{id}/songs', [PlaylistSongController::class, 'store']);
    Route::get('/playlists/{id}/songs', [PlaylistSongController::class, 'show']);
    Route::delete('/playlists/{id}/songs', [PlaylistSongController::class, 'destroy']);

    Route::post('/collaborations', [PlaylistCollaborationController::class, 'store']);
    Route::delete('/collaborations', [PlaylistCollaborationController::class, 'destroy']);
});
