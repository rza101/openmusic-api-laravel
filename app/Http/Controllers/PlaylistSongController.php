<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\PlaylistActivity;
use App\Models\PlaylistSong;
use App\Models\Song;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlaylistSongController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

    public function store(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'songId' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validated();

        $playlist = Playlist::with('PlaylistCollaborations')->find($id);

        if (!$playlist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found'
            ], 404);
        }

        $song = Song::find($validatedData['songId']);

        if (!$song) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Song not found'
            ], 404);
        }

        $user = Auth::user();

        $isCollaborator = $playlist->PlaylistCollaborations()
            ->where('playlist_id', $playlist->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($playlist->Owner->id != $user->id && !$isCollaborator) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist cannot be modified'
            ], 403);
        }

        PlaylistSong::create([
            'id' => 'playlist_song_' . $this->nanoid->generateId(32),
            'playlist_id' => $playlist->id,
            'song_id' => $song->id,
        ]);

        PlaylistActivity::create([
            'id' => 'playlist_activity_' . $this->nanoid->generateId(32),
            'playlist_id' => $playlist->id,
            'song_id' => $song->id,
            'user_id' => $user->id,
            'action' => 'add',
            'time' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Playlist song added successfully'
        ], 201);
    }

    public function show(string $id)
    {
        $playlist = Playlist::with(['PlaylistCollaborations', 'PlaylistSongs.Song'])->find($id);

        if (!$playlist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found'
            ], 404);
        }

        $user = Auth::user();

        $isCollaborator = $playlist->PlaylistCollaborations()
            ->where('playlist_id', $playlist->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($playlist->Owner->id != $user->id && !$isCollaborator) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist cannot be viewed'
            ], 403);
        }

        $playlistSongs = [];

        foreach ($playlist->PlaylistSongs as $playlistSong) {
            array_push(
                $playlistSongs,
                [
                    'id' => $playlistSong->Song->id,
                    'title' => $playlistSong->Song->title,
                    'performer' => $playlistSong->Song->performer,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'playlist' => [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                    'username' => $playlist->Owner->username,
                    'songs' => $playlistSongs,
                ]
            ]
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'songId' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        $playlist = Playlist::with('PlaylistCollaborations')->find($id);

        if (!$playlist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found'
            ], 404);
        }

        $user = Auth::user();

        $isCollaborator = $playlist->PlaylistCollaborations()
            ->where('playlist_id', $playlist->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($playlist->Owner->id != $user->id && !$isCollaborator) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist cannot be modified'
            ], 403);
        }

        PlaylistSong::where([
            ['playlist_id', $id],
            ['song_id', $validatedData['songId']],
        ])->delete();

        PlaylistActivity::create([
            'id' => 'playlist_activity_' . $this->nanoid->generateId(32),
            'playlist_id' => $id,
            'song_id' => $validatedData['songId'],
            'user_id' => $user->id,
            'action' => 'delete',
            'time' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Playlist song removed successfully'
        ]);
    }
}
