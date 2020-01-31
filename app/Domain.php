<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';
    public $timestamps = false;
    protected $fillable = [
        'id', 'name'
    ];
}
