<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exam;
use App\Student;
use App\Education;
use App\Role;
use App\Domain;
use App\User;

class AdminController extends Controller
{
    public function getExams(){
        $exams = [];
        if(request('education_id') == null){
            $exams = Exam::where('domain_id', request('domain_id'))->get();
        }else{
            $exams = Exam::where('education_id', request('education_id'))->where('domain_id', request('domain_id'))->get();
        }

        for ($i=0; $i < count($exams); $i++) {
            $exams[$i]['education'] = Education::where('id', $exams[$i]['education_id'])->get()->first();
            $exams[$i]['domain'] = Domain::where('id', $exams[$i]['domain_id'])->get()->first();
        }

        return json_encode($exams);
    }

    public function getEducations(){
        $educations = Education::where('domain_id', request('id'))->get()->toArray();

        for ($i=0; $i < count($educations); $i++) {
            $educations[$i]['domain'] = Domain::where('id', $educations[$i]['domain_id'])->get()->first();
        }

        return json_encode($educations);
    }

    public function getDomains(){
        $id = request('id');
        $domains = [];
        if($id == null){
            $domains = Domain::all()->toArray();
        }else{
            $domains = Domain::where('id', $id)->get()->toArray();
        }

        return json_encode($domains);
    }

    public function getUsers(){
        $id = request('id');
        $role_id = request('role_id');
        $users = [];
        if($id == null){
            $users = User::where('role_id', 2)->get()->toArray();
        }else{
            $users = User::where('role_id', ">", $role_id)->where('domain_id', $id)->get()->toArray();
        }

        for ($i=0; $i < count($users); $i++) {
            $users[$i]['domain'] = Domain::where('id', $users[$i]['domain_id'])->get()->first();
            $users[$i]['education'] = Education::where('id', $users[$i]['education_id'])->get()->first();
            $users[$i]['role'] = Role::where('id', $users[$i]['role_id'])->get()->first();
        }

        return json_encode($users);
    }

    public function copyExamById(){
        $currentExam = Exam::where('id', request('id'))->first();

        $newExam = $currentExam->replicate();
        $newExam['exam_states_id'] = 1;
        $newExam['name'] = request('newName');

        $newExam->save();

        return $newExam->id;
    }

    public function deleteExamById() {
        Exam::where('id', request('id'))->delete();
    }

    public function deleteStudentById() {
        Student::where('id', request('id'))->delete();
    }

    public function editStudentById() {
        $data['ov'] = request('ov');
        $data['name'] = request('name');
        $data['year'] = request('year');
        $student = Student::where('id', request('id'))->update($data);
    }

    public function deleteAdminById() {
        User::where('id', request('id'))->delete();
    }

    public function newDomain() {
        $data = array(
            'id' => request('id'),
            'name' => request('data')['name']
        );

        $domain = Domain::create($data);
        return $domain;
    }

    public function saveDomain(){
        $data = request('domain');
        $domain = Domain::where('id', $data['id'])->first();
        $domain->name = $data['name'];
        $domain->save();
    }

    public function deleteDomainById() {
        Domain::where('id', request('id'))->delete();
    }

    public function newEducation() {
        $data = array(
            'education_id' => request('id'),
            'name' => 'new education'
        );

        $education = Education::create($data);
        return $education;
    }

    public function editEducation() {
        $education = Education::where('id',  request('data')['id'])->first();
        $education['name'] = request('data')['name'];
        $education->save();
    }

    public function saveAdmin(){
        $data = request('admin');
        $admin = User::where('id', $data['id'])->first();
        $admin->username = $data['username'];
        $admin->education_id = $data['education_id'];
        $admin->domain_id = $data['domain_id'];
        $admin->save();
    }
}
