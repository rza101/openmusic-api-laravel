<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
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
                'message' => 'Invalid album data'
            ], 400);
        }

        $album = Album::create([
            'id' => 'album_' . $this->nanoid->generateId(32),
            ...$validator->validate()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'albumId' => $album->id
            ]
        ], 201);
    }

    public function show(string $id)
    {
        $album = Album::with('Songs:id,title,performer,album_id')->find($id);

        if ($album) {
            $album->Songs->each(function ($song) {
                $song->makeHidden(['album_id']);
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'album' => $album
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }
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
                'message' => 'Invalid album data'
            ], 400);
        }

        $album = Album::find($id);

        if ($album) {
            $album->update($validator->validate());

            return response()->json([
                'status' => 'success',
                'message' => 'Album updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }
    }

    public function destroy(string $id)
    {
        $album = Album::find($id);

        if ($album) {
            $album->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Album deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }
    }
}
