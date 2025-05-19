<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $songs = Song::select(['id', 'title', 'performer']);

        $title = $request->query('title');

        if (!empty($title)) {
            $songs->whereLike('title', "%{$title}%");
        }

        $performer = $request->query('performer');

        if (!empty($performer)) {
            $songs->whereLike('performer', "%{$performer}%");
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'songs' => $songs->get()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|string',
                'year' => 'required|integer|gte:1',
                'genre' => 'required|string',
                'performer' => 'required|string',
                'duration' => 'integer|gte:1',
                'albumId' => 'string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid song data'
            ], 400);
        }

        $nanoid = new Client();
        $validated = $validator->validate();

        if (isset($validated['albumId'])) {
            $validated['album_id'] = $validated['albumId'];
        }

        $song = Song::create([
            'id' => 'song_' . $nanoid->generateId(32),
            ...$validated
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'songId' => $song->id
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $song = Song::find($id);

        if ($song) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'song' => $song
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
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
                'title' => 'required|string',
                'year' => 'required|integer|gte:1',
                'genre' => 'required|string',
                'performer' => 'required|string',
                'duration' => 'integer|gte:1',
                'albumId' => 'string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid song data'
            ], 400);
        }

        $validated = $validator->validate();

        if (isset($validated['albumId'])) {
            $validated['album_id'] = $validated['albumId'];
        }

        $song = Song::find($id);

        if ($song) {
            $song->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Song updated successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $song = Song::find($id);

        if ($song) {
            $song->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Song deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
            ], 404);
        }
    }
}
