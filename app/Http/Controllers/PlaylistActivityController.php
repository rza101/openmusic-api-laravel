<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Support\Facades\Auth;

class PlaylistActivityController extends Controller
{
    public function show(string $id)
    {
        $user = Auth::user();

        $playlist = Playlist::with(['PlaylistActivities.User', 'PlaylistActivities.Song'])->find($id);

        if (!$playlist) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist not found'
            ], 404);
        }

        if ($playlist->Owner->id != $user->id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Playlist activities cannot be viewed'
            ], 403);
        }

        $playlistActivitiesData = $playlist->PlaylistActivities
            ->sortBy(function ($playlistActivity) {
                return $playlistActivity->time;
            });
        $playlistActivities = [];

        foreach ($playlistActivitiesData as $playlistActivity) {
            array_push(
                $playlistActivities,
                [
                    'username' => $playlistActivity->User->username,
                    'title' => $playlistActivity->Song->title,
                    'action' => $playlistActivity->action,
                    'time' => date('Y-m-d\TH:i:s.v\Z', strtotime($playlistActivity->time)),
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'playlistId' => $playlist->id,
                'activities' => $playlistActivities
            ]
        ]);
    }
}
