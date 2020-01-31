<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exam_State extends Model
{
    protected $fillable = [
        'name'
    ];
    protected $table = "exam_states";
    public $timestamps = false;
}
