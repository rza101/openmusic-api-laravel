<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authentication extends Model
{
    protected $fillable = ['access_token', 'refresh_token'];
    protected $primaryKey = null;

    public $incrementing = false;
    public $timestamps = false;
}
