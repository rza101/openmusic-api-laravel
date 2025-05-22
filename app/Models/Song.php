<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = ['id', 'title', 'year', 'genre', 'performer', 'duration', 'album_id'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function Album()
    {
        return $this->belongsTo(Album::class, 'album_id', 'id');
    }

    public function PlaylistSongs()
    {
        return $this->hasMany(PlaylistSong::class, 'playlist_id', 'id');
    }

    public function PlaylistActivities()
    {
        return $this->hasMany(PlaylistActivity::class, 'playlist_id', 'id');
    }
}
