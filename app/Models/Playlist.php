<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['id', 'name', 'owner'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function Owner()
    {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    public function PlaylistActivities()
    {
        return $this->hasMany(PlaylistActivity::class, 'playlist_id', 'id');
    }

    public function PlaylistCollaborations()
    {
        return $this->hasMany(PlaylistCollaboration::class, 'playlist_id', 'id');
    }

    public function PlaylistSongs()
    {
        return $this->hasMany(PlaylistSong::class, 'playlist_id', 'id');
    }
}
