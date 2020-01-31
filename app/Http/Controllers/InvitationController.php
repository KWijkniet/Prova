<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

use Carbon\Carbon;
use App\Invitation;
use App\Role;
use App\Education;
use App\Domain;

class InvitationController extends Controller
{
    public function getInvitations(){
        $domain_id = request('domain_id');
        $education_id = request('education_id');
        $invitations = [];

        if($domain_id == null && $education_id == null){
            //is ceo
            $invitations = Invitation::where('domain_id', '!=', null)->get()->toArray();
        }
        else{
            $invitations = Invitation::where('education_id', $education_id)->where('domain_id', $domain_id)->get()->toArray();
        }

        for ($i=0; $i < count($invitations); $i++) {
            $invitations[$i]['role'] = Role::where('id', $invitations[$i]['role_id'])->get()->first();
            $invitations[$i]['education'] = Education::where('id', $invitations[$i]['education_id'])->get()->first();
            $invitations[$i]['domain'] = Domain::where('id', $invitations[$i]['domain_id'])->get()->first();
        }

        return json_encode($invitations);
    }

    public function getRoles(){
        $id = request('id');
        $roles = [];
        if($id == null){
            $roles = Role::where('id', 2)->get()->toArray();
        }else{
            $roles = Role::where('id', '>', $id)->get()->toArray();
        }

        return json_encode($roles);
    }

    public function sendEmail(Request $request)
    {
        $invitation = Invitation::where('id', request('id'))->get()->first();
        $to_email = $invitation->email;
        $data = array("invitation_token" => $invitation->invitation_token, "link" => $request->getHost());

        Mail::send('emails.mail', $data, function($message) use ($to_email) {
            $message->to($to_email)
            ->subject('Examiner Registration');
            $message->from('examentool@gmail.com','Examen Afname');
        });
    }

    public function index()
    {
        $invitations = Invitation::all();
        return response()->json($invitations);
    }

    public function newInvitation(Request $request) {
        $data = array(
            'email' => request('data')['email'],
            'role_id' => request('data')['role_id'],
            'education_id' => request('data')['education_id'],
            'domain_id' => request('data')['domain_id'],
            'invitation_token' => substr(md5(rand(0, 9) . request('data')['email'] . time()), 0, 32),
            'created_at' => Carbon::now()->toDateTimeString()
        );
        // return $request;
        $rules = [
            'data.email' => 'unique:users,email',
        ];
        
        $customMessages = [
            'unique' => 'The email already exists'
        ];
        
        $this->validate($request, $rules, $customMessages);
        $user = Invitation::where('email', $data['email'])->first();
        if($user===null){
            $invitation = Invitation::create($data);
            $invitation = $invitation->refresh();
            $invitation['role'] = Role::where('id', $data['role_id'])->get()->first();
            $invitation['education'] = Education::where('id', $data['education_id'])->get()->first();
            $invitation['domain'] = Domain::where('id', $data['domain_id'])->get()->first();
            return $invitation;
        } else {
            return response()->json(['error' => 'Conflict', 'message' => 'User with that e-mail is already invited!'], 409);
        }
    }

    public function saveInvitation(){
        $data = request('invitation');
        $invitation = Invitation::where('id', $data['id'])->first();
        $invitation->email = $data['email'];
        //$invitation->role_id = $data['role_id'];
        $invitation->save();
    }

    public function deleteInvitationById() {
        Invitation::where('id', request('id'))->delete();
    }
}
