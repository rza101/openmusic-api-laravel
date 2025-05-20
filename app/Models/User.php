<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
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
}
