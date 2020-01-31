<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'name', 'year', 'exam_states_id', 'template', 'education_id', 'domain_id'
    ];
    protected $table = "exams";
    public $timestamps = false;
}
