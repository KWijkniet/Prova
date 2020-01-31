<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ov', 'name', 'year', 'education_id', 'domain_id'
    ];

    protected $table = "students";
    public $timestamps = false;
}
