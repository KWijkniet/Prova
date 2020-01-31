<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Instance;
use App\User;
use App\Result;

class CombineController extends Controller
{
    public function getInstances($id)
    {
        $instance = Instance::where('id', $id)->first();
        $instances = Instance::where('student_id', $instance->student_id)->
        where('exam_id', $instance->exam_id)->get()->toArray();

        for($i = 0; $i < count($instances); $i++){
            $instances[$i]['examiner'] = User::where('id', $instances[$i]['examiner_id'])->first();
        }

        return $instances;
    }

    public function createResult(){
        $data = request()['data'];
        Result::create($data);
    }
}
