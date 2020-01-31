<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'email', 'role_id', 'education_id', 'domain_id', 'invitation_token', 'created_at'
    ];
}
