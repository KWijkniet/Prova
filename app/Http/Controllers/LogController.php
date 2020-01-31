<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Log;
use App\Role;
use App\User;

class LogController extends Controller
{
    public function createLog()
    {
        $data['user_id'] = request('id');
        $data['username'] = request('name');
        $data['role_id'] = request('role_id');
        $data['action'] = request('action');
        Log::create($data);
    }

    public function getLog() {
        $user = User::where('id', request('id'))->first();
        $logs = Log::where('role_id', ">",$user['role_id'])->orderBy('id')->get()->toArray();
        for ($i=0; $i < count($logs); $i++) {
            $logs[$i]['role'] = role::select('name')->where('id', $logs[$i]['role_id'])->first();
        }
        return json_encode($logs);
    }
}
