<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

    public function index()
    {
        return response(null, 404);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'year' => 'required|integer|gte:1',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters'
            ], 400);
        }

        $validatedData = $validator->validate();

        $album = Album::create([
            'id' => 'album_' . $this->nanoid->generateId(32),
            ...$validatedData
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'albumId' => $album->id
            ]
        ], 201);
    }

    public function storeCoverImage(Request $request, string $id)
    {
        $fileValidator = Validator::make(
            $request->all(),
            [
                'cover' => 'required|image',
            ]
        );

        if ($fileValidator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters'
            ], 400);
        }

        $fileSizeValidator = Validator::make(
            $request->all(),
            [
                'cover' => 'max:512',
            ]
        );

        if ($fileSizeValidator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Cover image size too large'
            ], 413);
        }

        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }

        $coverFile = $request->file('cover');
        $coverFilename = $coverFile->hashName();
        $coverFileExt = $coverFile->extension();

        $coverFile->storeAs('album_covers', $coverFilename . '.' . $coverFileExt);

        $album->cover = $coverFilename . '.' . $coverFileExt;
        $album->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Album cover uploaded'
        ], 201);
    }

    public function show(string $id)
    {
        $album = Album::with('Songs:id,title,performer,album_id')->find($id);

        if (!$album) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }

        $album->Songs->each(function ($song) {
            $song->makeHidden(['album_id']);
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'album' => [
                    'id' => $album->id,
                    'name' => $album->name,
                    'year' => $album->year,
                    'coverUrl' => $album->cover ? route('albums.showCoverImage', $album->id) : null,
                    'songs' => $album->Songs
                ]
            ]
        ]);
    }

    public function showCoverImage(string $id)
    {
        $album = Album::find($id);

        if (!$album || !Storage::exists('album_covers/' . $album->cover)) {
            return response(null, 404);
        }

        $coverFileExt = File::guessExtension(
            storage_path('app/private/album_covers/' . $album->cover)
        );

        return Storage::download(
            'album_covers/' . $album->cover,
            $album->name . '.' . $coverFileExt,
        );
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'year' => 'required|integer|gte:1',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters'
            ], 400);
        }

        $validatedData = $validator->validate();

        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }

        $album->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Album updated successfully'
        ]);
    }

    public function destroy(string $id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }

        $album->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Album deleted successfully'
        ]);
    }
}
