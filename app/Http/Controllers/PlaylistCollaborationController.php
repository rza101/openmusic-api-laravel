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
                'message' => 'Invalid collaboration data',
            ], 400);
        }

        $validated = $validator->validate();

        $playlist = Playlist::find($validated['playlistId']);

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

        $collaboratorUser = User::find($validated['userId']);

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
                'message' => 'Collaboration already exists',
            ], 400);
        }

        $collaboration = PlaylistCollaboration::create([
            'id' => 'collaboration_' . $this->nanoid->generateId(32),
            'playlist_id' => $playlist->id,
            'user_id' => $collaboratorUser->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'collaborationId' => $collaboration->id,
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
                'message' => 'Invalid collaboration data',
            ], 400);
        }

        $validated = $validator->validate();
        $user = Auth::user();

        $collaboration = PlaylistCollaboration::with('Playlist.Owner')
            ->where('playlist_id', $validated['playlistId'])
            ->where('user_id', $validated['userId'])
            ->first();

        if ($collaboration) {
            if ($collaboration->Playlist->Owner->id != $user->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Playlist collaboration cannot be modified',
                ], 403);
            }

            $collaboration->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Playlist collaboration deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Collaboration not found'
            ], 404);
        }
    }
}
