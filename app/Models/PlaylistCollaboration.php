<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistCollaboration extends Model
{
    protected $fillable = ['id', 'playlist_id', 'user_id'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function Playlist()
    {
        return $this->belongsTo(Playlist::class, 'playlist_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
