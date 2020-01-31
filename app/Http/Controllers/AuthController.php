<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Request;

use App\User;
use App\Invitation;
use App\Session;
use App\Domain;
use App\Education;

use DateTime;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        date_default_timezone_set('Europe/Amsterdam');
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Conflict', 'message' => 'Email or password is incorrect'], 409);
        }
        $user = User::where('email', $credentials['email'])->where('password', $credentials['password'])->first();

        return $this->AttemptLogin($user, $token);
    }

    public function register(Request $request)
    {
        $invitation = Invitation::where('invitation_token', request('token'))->first();
        if(!$invitation){
            return response()->json(['error' => 'Conflict', 'message' => "Token doesn't exist!"], 409);
        }

        $databaseDateTime = $invitation['created_at'];
        $today = Carbon::today()->toDateString();
        $timediff = strtotime($today) - strtotime($databaseDateTime);

        if($timediff > 86400){
            $invitation->delete();
            return response()->json(['error' => 'Conflict', 'message' => 'Invitation Token has been expired!'], 409);
        }

        $invitation->email = strtolower($invitation->email);

        $this->validate(request(), [
            'data.username' => 'required',
            'data.email' => 'required|in:'.$invitation->email,
            'data.password' => 'required|same:data.confirm',
            'data.confirm' => 'required'
        ]);

        $credentials = array();
        $credentials['username'] = request('data')['username'];
        $credentials['email'] = request('data')['email'];
        $credentials['password'] = request('data')['password'];
        $credentials['role_id'] = $invitation->role_id;
        $credentials['education_id'] = $invitation->education_id;
        $credentials['domain_id'] = $invitation->domain_id;

        if(User::where('email', $credentials['email'])->first()){
            return response()->json(['error' => 'Conflict', 'message' => 'User with those credentials already exists!'], 409);
        }

        $user = User::create($credentials);
        if($user){
            if (!$token = auth('api')->attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Login failed'], 401);
            }
            //Deletes the invitation
            if($returnData = $this->AttemptLogin($user, $token)){
                $invitation->delete();
                return $returnData;
            }else{
                return response()->json(['error' => 'Conflict', 'message' => 'Could not login new user!'], 409);
            }
        }else{
            return response()->json(['error' => 'Conflict', 'message' => 'Could not create user!'], 409);
        }
    }

    private function AttemptLogin($user, $token){
        date_default_timezone_set('Europe/Amsterdam');
        $expire_date = date("Y-m-d H:i:s",time() + 3600);
        $expire_date = new DateTime($expire_date);

        $user = auth('api')->user();
        $session = Session::where('user_id', $user->id)->whereDate('expiration_date', '>=', Carbon::now('Europe/Amsterdam'))->first();

        if(!$session){
            $session = Session::create(array('user_id' => $user->id, 'web_token' => $token, 'expiration_date' => $expire_date));
        }

        $user->education = Education::where('id', $user->education_id)->get()->first();
        $user->domain = Domain::where('id', $user->domain_id)->get()->first();

        $data = array('session' => $session, 'user' => $user);
        return $this->respondWithToken($token, $data);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSession(){
        //check expiration_date
        $userId = request('userId');
        $session = Session::where('user_id', $user->id)->whereDate('expiration_date', '>=', Carbon::now('Europe/Amsterdam'));
        return json_encode($session);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSession(){
        //check expiration_date
        $userId = request('userId');
        date_default_timezone_set('Europe/Amsterdam');

        $session = Session::where('user_id', $userId)->whereDate('expiration_date', '>=', Carbon::now('Europe/Amsterdam'))->first();
        if($session){
            $session->expiration_date = date("Y-m-d h:i:s",time() + 3600); // 2 uur
            $session->save();

            return json_encode($session);
        }else{
            return "Session not found";
        }
    }

    public function getSession(){
        //check expiration_date
        $userId = request('userId');

        $session = Session::where('user_id', $userId)->first();
        return json_encode($session);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSession(){
        $userId = request('userId');
        Session::where('user_id', $userId)->delete();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $data)
    {
        return response()->json([
            'access_token' => $token,
            'data' => json_encode($data),
            'token_type' => 'bearer',
            'expires_in' => time()+3600 // 2 uur
        ]);
    }
}
