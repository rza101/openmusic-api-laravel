<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['id', 'name', 'year'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function Songs()
    {
        return $this->hasMany(Song::class, 'album_id', 'id');
    }

    public function UserAlbumLikes()
    {
        return $this->hasMany(UserAlbumLike::class, 'album_id', 'id');
    }
}
