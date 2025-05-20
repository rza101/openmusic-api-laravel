<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistSong extends Model
{
    protected $fillable = ['id', 'playlist_id', 'song_id'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function Playlist()
    {
        return $this->belongsTo(Playlist::class, 'playlist_id', 'id');
    }

    public function Song()
    {
        return $this->belongsTo(Song::class, 'song_id', 'id');
    }
}
