<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\PlaylistCollaboration;
use App\Models\User;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlaylistCollaborationController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'playlistId' => 'required|string',
                'userId' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        $playlist = Playlist::find($validatedData['playlistId']);

        if (!$playlist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found',
            ], 404);
        }

        $user = Auth::user();

        if ($playlist->Owner->id != $user->id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist collaboration cannot be modified',
            ], 403);
        }

        $collaboratorUser = User::find($validatedData['userId']);

        if (!$collaboratorUser) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User not found',
            ], 404);
        }

        $isCollaborationExists = PlaylistCollaboration::where('playlist_id', $playlist->id)
            ->where('user_id', $collaboratorUser->id)
            ->exists();

        if ($isCollaborationExists) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist collaboration already exists',
            ], 400);
        }

        $playlistCollaboration = PlaylistCollaboration::create([
            'id' => 'collaboration_' . $this->nanoid->generateId(32),
            'playlist_id' => $playlist->id,
            'user_id' => $collaboratorUser->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'collaborationId' => $playlistCollaboration->id,
            ]
        ], 201);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'playlistId' => 'required|string',
                'userId' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters',
            ], 400);
        }

        $validatedData = $validator->validate();

        $playlistCollaboration = PlaylistCollaboration::with('Playlist.Owner')
            ->where('playlist_id', $validatedData['playlistId'])
            ->where('user_id', $validatedData['userId'])
            ->first();

        if ($playlistCollaboration) {
            $user = Auth::user();

            if ($playlistCollaboration->Playlist->Owner->id != $user->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Playlist collaboration cannot be modified',
                ], 403);
            }

            $playlistCollaboration->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Playlist collaboration deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Playlist collaboration not found'
            ], 404);
        }
    }
}
