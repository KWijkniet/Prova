<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exam;
use App\Student;
use App\Education;
use App\Instance;
use App\Planned;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function getExams()
    {
        $exams = Exam::where('exam_states_id', 4)->where('education_id', request('id'))->get();
        for ($i=0; $i < count($exams); $i++) {
            $exams[$i]['education'] = Education::where('id', $exams[$i]['education_id'])->first();
        }
        return json_encode($exams);
    }

    public function getStudents()
    {
        $plannedExams = Planned::whereDate('date', Carbon::today())->where('exam_id', request('exam_id'))->get();
        $list = [];
        for ($i=0; $i < count($plannedExams); $i++) {
            $student = Student::where('id', $plannedExams[$i]['student_id'])->where('education_id', request('education_id'))->where('domain_id', request('domain_id'))->first();
            if($student){
                array_push($list, $student);
            }
        }
        return json_encode($list);
    }

    public function getEducations()
    {
        $educations = Education::where('domain_id', request('id'))->get();
        return json_encode($educations);
    }
    public function startExam(){
        $myInstance = Instance::
        where('examiner_id', request('data.examiner_id'))
        ->where('exam_id', request('data.exam_id'))
        ->where('student_id', request('data.student_id'))
        ->where('end_date', Null)
        ->first();

        $instances = Instance::
            where('examiner_id', request('data.examiner_id'))
            ->where('exam_id', request('data.exam_id'))
            ->where('end_date', null)
            ->get()->toArray();

        foreach ($instances as $instance) {
            if($instance['student_id'] != request('data.student_id')){
                $returnData['status'] = 'error';
                $returnData['message'] = 'There is already an active exam, please finish the previous exam';
                return json_encode($returnData);
            }
        }

        $myInstance = Instance::
            where('examiner_id', request('data.examiner_id'))
            ->where('exam_id', request('data.exam_id'))
            ->where('student_id', request('data.student_id'))
            ->where('end_date', null)
            ->first();

        if(!$myInstance){
            $myInstance = Instance::create(request('data'));
        }

        $returnData['status'] = 'succeed';
        $returnData['message'] = $myInstance['id'];
        return json_encode($returnData);
    }

    public function getStartedInstance() {
        $myInstance = Instance::
        where('examiner_id', request('id'))
        ->where('end_date', Null)
        ->first();

        return json_encode($myInstance);
    }

    public function checkExam(){
        $instances = Instance::
            where('student_id', request('data.student_id'))
            ->where('exam_id', request('data.exam_id'))
            ->where('examiner_id','!=', request('data.examiner_id'))
            ->where('end_date', null)
            ->where('id', '!=', request('id'))
            ->get()->toArray();

        return count($instances);
    }

    public function delInstance($id = null){
        if($id == null){
            Instance::destroy(request('id'));
        }else{
            Instance::destroy($id);
        }
    }
}
