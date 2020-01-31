<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'action', 'role_id', 'username'
    ];
    protected $table = "log";
}
