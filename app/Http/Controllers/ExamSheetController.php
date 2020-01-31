<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exam;
use App\Instance;
use Carbon\Carbon;

class ExamSheetController extends Controller
{
    public function getExamById($id)
    {
        $exam = Exam::where('id', $id)->first();
        return $exam;
    }
    public function checkCombine(){
        $myInstance = Instance::where('id', request('instance_id'))->first();
        $instances = Instance::where('student_id', $myInstance['student_id'])
                            ->where('exam_id', $myInstance['exam_id'])
                            ->where('end_date', null)
                            ->get()
                            ->toArray();
        return count($instances);
    }
    public function saveResults(){
        $responseData = array('status' => 'failure', 'message' => 'user is not the first to submit');  
        $myInstance = Instance::where('id', request('instance_id'))->first();
        $myInstance['answers'] = request('answers');
        $myInstance['end_date'] = Carbon::today();
        $myInstance->save();
        $instances = Instance::where('student_id', $myInstance['student_id'])
                            ->where('exam_id', $myInstance['exam_id'])
                            ->where('id', '!=', $myInstance['id'])
                            ->get()
                            ->toArray();
        $isFirst = true;
        foreach($instances as $instance){
            if($instance['end_date'] != null){
                $isFirst = false;
            }
        }
        if($isFirst){
            $responseData['status'] = 'success';
            $responseData['message'] = 'user is first to submit exam';
        }
        return json_encode($responseData);

    }
    public function saveAnswers()
    {
        $instance = Instance::where('id', request('instance_id'))->first();
        $instance['answers'] = request('answers');
        $instance->save();
    }


    public function getInstance(){
        $instance = Instance::where('id', request('id'))->where('examiner_id', request('examiner_id'))->first();
        return json_encode($instance);
    }

}
