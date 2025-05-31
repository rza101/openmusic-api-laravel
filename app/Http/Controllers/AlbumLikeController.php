<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\UserAlbumLike;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class AlbumLikeController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

    public function store(string $id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }

        $user = Auth::user();

        $userAlbumLikeCount = UserAlbumLike::where([
            ['user_id', $user->id],
            ['album_id', $album->id],
        ])->count();

        if ($userAlbumLikeCount > 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album already liked'
            ], 400);
        }

        UserAlbumLike::create([
            'id' => $this->nanoid->generateId(32),
            'user_id' => $user->id,
            'album_id' => $album->id,
        ]);

        Redis::del('album_likes_' . $album->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Album liked'
        ], 201);
    }

    public function show(string $id)
    {
        $cachedUserAlbumLikeCount = Redis::get('album_likes_' . $id);

        if ($cachedUserAlbumLikeCount && is_numeric($cachedUserAlbumLikeCount)) {
            return response()->json(
                [
                    'status' => 'success',
                    'data' => [
                        'likes' => intval($cachedUserAlbumLikeCount),
                    ]
                ],
                200,
                [
                    'X-Data-Source' => 'cache'
                ]
            );
        }

        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not found'
            ], 404);
        }

        $userAlbumLikeCount = UserAlbumLike::where('album_id', $album->id)->count();

        Redis::set(
            'album_likes_' . $id,
            $userAlbumLikeCount,
            'EX',
            1800
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'likes' => $userAlbumLikeCount,
            ]
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

        $user = Auth::user();

        $userAlbumLike = UserAlbumLike::where([
            ['user_id', $user->id],
            ['album_id', $album->id],
        ])->first();

        if (!$userAlbumLike) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Album not liked'
            ], 400);
        }

        $userAlbumLike->delete();

        Redis::del('album_likes_' . $album->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Album unliked'
        ]);
    }
}
