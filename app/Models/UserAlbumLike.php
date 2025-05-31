<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAlbumLike extends Model
{
    protected $fillable = ['id', 'user_id', 'album_id'];
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Album()
    {
        return $this->belongsTo(Album::class, 'album_id', 'id');
    }
}
