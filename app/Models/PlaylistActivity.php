<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistActivity extends Model
{
    protected $fillable = ['id', 'playlist_id', 'song_id', 'user_id', 'action', 'time'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function Playlist()
    {
        return $this->belongsTo(Playlist::class, 'playlist_id', 'id');
    }
}
