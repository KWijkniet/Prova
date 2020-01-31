<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Education;
use App\Domain;

class EducationController extends Controller
{
    public function getEducations(){
        $educations = Education::where('domain_id', request('id'))->get()->toArray();

        for ($i=0; $i < count($educations); $i++) {
            $educations[$i]['domain'] = Domain::where('id', $educations[$i]['domain_id'])->get()->first();
        }

        return json_encode($educations);
    }

    public function deleteEducationById() {
        Education::where('id', request('id'))->delete();
    }

    public function saveEducation(){
        $data = request('education');
        $education = Education::where('id', $data['id'])->first();
        $education->name = $data['name'];
        $education->save();
    }

    public function newEducation() {
        $data = array(
            'domain_id' => request('data')['domain_id'],
            'name' => request('data')['name']
        );

        $education = Education::create($data);
        $education['domain'] = Domain::where('id', $education['domain_id'])->get()->first();

        return $education;
    }
}
