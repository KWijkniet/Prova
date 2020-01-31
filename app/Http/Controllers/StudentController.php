<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Student;
use App\Info;
use App\Exam;
use App\Result;
use App\Planned;
use App\Education;
use App\Domain;

use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function index()
    {
        $students = null;
        if(request('education_id') != null){
            $students = Student::where('education_id', request('education_id'))->where('domain_id', request('domain_id'))->get()->toArray();
        }else{
            $students = Student::where('domain_id', request('domain_id'))->get()->toArray();
        }

        for ($i=0; $i < count($students); $i++) {
            $students[$i]['planned'] = Planned::where('student_id', $students[$i]['id'])->get()->toArray();
            $students[$i]['education'] = Education::where('id', $students[$i]['education_id'])->first();
            $students[$i]['domain'] = Domain::where('id', $students[$i]['domain_id'])->first();
            $results = Result::where('student_id', $students[$i]['id'])->get()->toArray();
            for ($r=0; $r < count($results); $r++) {
                $results[$r]['creation_date'] = date("d-m-Y", strtotime($results[$r]['creation_date']));
                $results[$r]['examName'] = Exam::where('id', $results[$r]['exam_id'])->first()->name;
            }
            $students[$i]['results'] = $results;
        }
        return response()->json($students);
    }

    public function getExams(){
        $exams = Exam::where('education_id', request('education_id'))->where('domain_id', request('domain_id'))->where('exam_states_id', "4")->get()->toArray();
        return response()->json($exams);
    }

    public function getEducations(){
        $educations = Education::where('domain_id', request('domain_id'))->get()->toArray();
        return response()->json($educations);
    }

    public function saveStudent(){
        $data = request('student');
        $student = Student::where('id', $data['id'])->first();
        $student->name = $data['name'];
        $student->ov = $data['ov'];
        $student->year = $data['year'];
        $student->education_id = $data['education_id'];
        $student->save();

        $results = Result::where('student_id', $student->id)->get()->toArray();
        for ($r=0; $r < count($results); $r++) {
            $results[$r]['creation_date'] = date("d-m-Y", strtotime($results[$r]['creation_date']));
            $results[$r]['examName'] = Exam::where('id', $results[$r]['exam_id'])->first()->name;
        }
        $student->education = Education::where('id', $student->education_id)->first();
        $student->domain = Domain::where('id', $student->domain_id)->first();
        $student->results = $results;
        return response()->json($student);
    }

    public function viewTakenExam() {
        $result = Result::where('id', request('id'))->first();
        return response()->json($result);
    }

    public function getPlannedExamsOfStudent() {
        $planned = Planned::where('student_id', request('student_id'))->get();

        for ($i=0; $i < Count($planned); $i++) {
            $examName = Exam::select('name', 'id')->where('id', '=', $planned[$i]['exam_id'])->pluck('name')->toArray();
            // $planned[$i]['date'] = date("d-m-Y", strtotime($planned[$i]['date']));
            $planned[$i]['examName'] = $examName[0];
        }
        return json_encode($planned);
    }

    public function createPlannedExam()
    {
        $data['student_id'] = request('student_id');
        $data['exam_id'] = request('exam_id');
        $data['date'] = request('date');
        $planned = Planned::create($data);
        return json_encode($planned);
    }

    public function deletePlannedExamById() {
        Planned::where('id', request('id'))->delete();
    }

    public function upload(){
        $data = json_decode(request('data'),true);
        if ($data && Count($data) > 0) {
            for ($i=0; $i < Count($data); $i++) {
                if (array_key_exists("Nummer",$data[$i]) && array_key_exists("Roepnaam",$data[$i]) && array_key_exists("Achternaam",$data[$i]) && array_key_exists("Jaar",$data[$i])) {
                    $newData['ov'] = $data[$i]['Nummer'];
                    $newData['name'] = $data[$i]['Roepnaam'] . ' ' . $data[$i]['Tussenvoegsel'] . ' ' . $data[$i]['Achternaam'];
                    $newData['year'] = $data[$i]['Jaar'];
                    $newData['domain_id'] = $data[$i]['domain_id'];
                    $newData['education_id'] = $data[$i]['education_id'];
                    Student::create($newData);
                } else {
                    return "invalid";
                }
            }
        } else {
            return "invalid";
        }

    }

    public function savePlannedExamById() {
        $data['student_id'] = request('student_id');
        $data['exam_id'] = request('exam_id');
        $data['date'] = request('date');

        Planned::where('id', request('id'))->update($data);
    }

    public function getResult($id) {
        $result = Result::where('id', $id)->first();
        return json_encode($result);
    }
    public function getExamName($id){
        return Exam::select('name')->where('id', $id)->pluck('name')->first();
    }

}
