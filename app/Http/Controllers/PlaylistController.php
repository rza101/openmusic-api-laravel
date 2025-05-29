<?php

namespace App\Http\Controllers;

use App\Jobs\ExportPlaylistEmailJob;
use App\Models\Playlist;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlaylistController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

    public function index()
    {
        $user = Auth::user();

        $playlistsData = Playlist::with(['PlaylistCollaborations', 'Owner'])
            ->where('owner', $user->id)
            ->orWhereHas('PlaylistCollaborations',  function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();
        $playlists = [];

        foreach ($playlistsData as $playlist) {
            array_push($playlists, [
                'id' => $playlist->id,
                'name' => $playlist->name,
                'username' => $playlist->Owner->username,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'playlists' => $playlists
            ]
        ]);
    }

    public function export(Request $request, string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'targetEmail' => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        $playlist = Playlist::with(['PlaylistCollaborations', 'PlaylistSongs.Song'])->find($id);

        if (!$playlist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found'
            ], 404);
        }

        $user = Auth::user();

        if ($playlist->Owner->id != $user->id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist cannot be exported'
            ], 403);
        }

        ExportPlaylistEmailJob::dispatch(
            $playlist,
            $validatedData['targetEmail'],
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Playlist exported successfully'
        ], 201);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        $user = Auth::user();
        $playlist = Playlist::create([
            'id' => 'playlist_' . $this->nanoid->generateId(32),
            'name' => $validatedData['name'],
            'owner' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'playlistId' => $playlist->id
            ]
        ], 201);
    }

    public function destroy(string $id)
    {
        $playlist = Playlist::find($id);

        if ($playlist) {
            $user = Auth::user();

            if ($playlist->Owner->id != $user->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Playlist cannot be modified'
                ], 403);
            }

            $playlist->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Playlist deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found'
            ], 404);
        }
    }
}
