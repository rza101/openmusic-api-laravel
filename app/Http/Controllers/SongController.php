<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SongController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

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
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        if (isset($validatedData['albumId'])) {
            $validatedData['album_id'] = $validatedData['albumId'];
        }

        $song = Song::create([
            'id' => 'song_' . $this->nanoid->generateId(32),
            ...$validatedData
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'songId' => $song->id
            ]
        ], 201);
    }

    public function show(string $id)
    {
        $song = Song::find($id);

        if ($song) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'song' => $song
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
            ], 404);
        }
    }

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
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        if (isset($validatedData['albumId'])) {
            $validatedData['album_id'] = $validatedData['albumId'];
        }

        $song = Song::find($id);

        if ($song) {
            $song->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Song updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
            ], 404);
        }
    }

    public function destroy(string $id)
    {
        $song = Song::find($id);

        if ($song) {
            $song->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Song deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
            ], 404);
        }
    }
}
