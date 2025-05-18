<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response(null, 404);
    }

    /**
     * Store a newly created resource in storage.
     */
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

        $nanoid = new Client();

        $album = Album::create([
            'id' => 'album_' . $nanoid->generateId(32),
            ...$validator->validate()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'albumId' => $album->id
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $album = Album::find($id);

        if ($album) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'album' => $album
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
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
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $album = Album::find($id);

        if ($album) {
            $album->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Album deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }
    }
}
