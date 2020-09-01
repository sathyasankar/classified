<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Http\Location;
use App\Http\Controllers\Controller;
use Auth;
use Response;
use Validator;
use Hash;
use URL;
use DB;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use App\User;
use Config;
class AuthController extends Controller
{
    public $successStatus      = 200;
    public $errorStatus        = 401;
    public $validationError    = 422;
    public function __construct()
    {
        $this->user             = new User();
    }
 
   
    public function login(Request $request)
    {
        
        // print_r("hi");exit;
        
         //return $this->user->listall(); 
        
       // return URL::to('/');
       $authKey     = Config::get('custom.authKey');
        $validation = Validator::make($request->all(), [
                 'phone'=>'required',
                 'password'=>'required'
             ]);
        
        if ($validation->fails() ) 
        {
             $errors = $validation->errors()->all();
            return response()->json([
                        'status' => 'error',
                        'message' => $errors,
                         'code'=>'401',
                            ], $this->validationError);
        }
        else
        { 
           // auth codes  
             $http = new Client;
//$response = $http->post(URL::to('/') . '/oauth/token', [
            try {
               $response = $http->post('http://localhost/classified/api/oauth/token', [
                    'form_params' => [
                        'grant_type' => 'password',
                       'client_id' => $authKey['CLIENT_ID'],
                       'client_secret' => $authKey['CLIENT_SECRET'],
                      /*  'client_id' => 1,
                        'client_secret' => 'IrdgZck1YG2C0YzRmfgSZYEsfWCgf4hqRnU4qJzR',*/
                        'username' => request('phone'),
                        'password' => Hash::make(request('password')),
                        'scope' => '',
                    ],
                        //'http_errors' => false
                ]);
                 
                $response       = json_decode((string) $response->getBody(), true);
                $userDetail     = User::where('phone', $request->phone)->first();                            

                $response['user_id'] = $userDetail->id;      
                $response['user_details'] = array(
                    'user_id'=>$userDetail->id,
                    'user_name'=>$userDetail->name,);
                //get user profile
                return $response;
            }//try
            catch (\Exception $e) {
              return $e;
                if ($e->hasResponse()) {
                   // return $e;
                    $errorResponse = json_decode((string) $e->getResponse()->getBody(), true);
                   // return response()->json($errorResponse,401);
                    return response()->json([
                        'status' => 'error',
                        'message' =>'The user credentials were incorrect.',
                         'code'=>'401',
                            ], 401);
                }
            }
            
        }
    // return response()->json($this->content, $status);    
    }
    
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    protected function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone'=>'required|unique:users',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function register(Request $request)
    {
        return User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make(request('password')),
            'phone' => request('phone'),
        ]);
    }
}