<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exam;
use App\Exam_State;
use App\Education;
use App\Domain;

class ExamCreatorController extends Controller
{
    public function getExamById(){
        $exam = Exam::where('id', request('id'))->first();
        $exam['education'] = Education::where('id', $exam['education_id'])->get()->first();
        $exam['domain'] = Domain::where('id', $exam['domain_id'])->get()->first();
        return $exam;
    }

    public function getExamStates() {
        $exam_states = Exam_State::all();
        return json_encode($exam_states);
    }

    public function getEducations(){
        $educations = Education::where('domain_id', request('id'))->get();
        return json_encode($educations);
    }

    public function save()
    {
        $data['name'] = request('name');
        $data['year'] = request('year');
        $data['exam_states_id'] = request('exam_state');
        $data['education_id'] = request('education_id');
        $data['domain_id'] = request('domain_id');
        $data['template'] = request('template');

        if(request('id')){
            $exam = Exam::where('id', request('id'))->update($data);
            return json_encode($exam);
        }else{
            $exam = Exam::create($data);
            return json_encode($exam);
        }
    }
}
