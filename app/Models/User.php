<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['id', 'username', 'password', 'fullname',];
    protected $hidden = ['password'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function Playlists()
    {
        return $this->hasMany(Playlist::class, 'owner', 'id');
    }

    public function PlaylistCollaborations()
    {
        return $this->hasMany(PlaylistCollaboration::class, 'user_id', 'id');
    }

    public function PlaylistActivities()
    {
        return $this->hasMany(PlaylistActivity::class, 'user_id', 'id');
    }

    public function UserAlbumLikes()
    {
        return $this->hasMany(UserAlbumLike::class, 'user_id', 'id');
    }
}
