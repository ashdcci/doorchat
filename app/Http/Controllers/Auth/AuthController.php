<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Auth;
use Illuminate\Http\Request;
use Response;
use Illuminate\Validation\Factory;
use Validator;
use App\User;
use DB;

class AuthController extends Controller {

  /*
  |--------------------------------------------------------------------------
  | Registration & Login Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users, as well as the
  | authentication of existing users. By default, this controller uses
  | a simple trait to add these behaviors. Why don't you explore it?
  |
  */

  use AuthenticatesAndRegistersUsers;

  /**
   * Create a new authentication controller instance.
   *
   * @param  \Illuminate\Contracts\Auth\Guard  $auth
   * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
   * @return void
   */
  public function __construct(Guard $auth, Registrar $registrar)
  {
    $this->auth = $auth;
    $this->registrar = $registrar;

    $this->middleware('guest', ['except' => 'getLogout']);
  }


  /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        return Validator::make($data, [
            'fname' => 'required|max:255',
            'lname' =>'required|max:255',
            'email_address' => 'required|email|max:255|unique:tbl_user_register',
            'password' => 'required|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
      
        return User::create([
            'fname' => $data['fname'],
            'lname' => $data['lname'],
            'email_address' => $data['email_address'],
            'password' => bcrypt($data['password']),
        ]);
    }
     public function authenticate()
    {

      
        if (Auth::attempt(['email_address' => Request::input('email'), 'password' => Request::input('password')]))
        {
            return 'Register';
        }else{
            return 'Not Register';
        }
    }

    public function postRegister(Request $request){
      


      $v = Validator::make($request->all(), [
                'fullname' => 'required|max:255',
                'username' =>'required|max:255|unique:tbl_user_register',
                'email' => 'required|email|max:255|unique:tbl_user_register',
                'gender'=>'required',
                'image'=>'required',
                'lat'=>'required',
                'lng'=>'required',
                'password' => 'required|min:6',
        ]);

    if ($v->fails())
    {
        return Response::json(array(
            'error'=>true,
            'content'=>$v->errors()
            ),200);
    }else{
      //set file upload 
      $destinationPath =   base_path() . '/public/uploads/user_profile/';
             $file = $request->file('image');
            
             if (!($request->hasFile('image'))) {
                return Response::json(array(
                      'error' => true,
                      'response' =>'File Not Exist'
                      ),
                      200
                      );
         }else{
          $ext = $request->file('image')->getClientOriginalExtension();
                     if(!($ext=='jpg' || $ext=='png' || $ext=='jpeg' || $ext=='gif' || $ext=='png')){
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'File Type not Supported',
                            'token'=>$request->input('token')
                            ),200);

                     }
          //move file
            $fileName =rand(11111,99999).'.'.$request->file('image')->getClientOriginalExtension();
            $fll =  $request->file('image')->move($destinationPath, $fileName);
            $path ='http://'.$_SERVER['HTTP_HOST'].'/projects/doorchat/public/uploads/user_profile/'.$fileName;
         }

      $user_token =  bcrypt(rand());
      $url = 'http://maps.google.com/maps/api/geocode/json?latlng='.trim($request->input('lat')).','.trim($request->input('lng')).'&sensor=false'; 
                                      $get     = file_get_contents($url);
                                      $geoData = json_decode($get);
                                      if (json_last_error() !== JSON_ERROR_NONE) {
                                         $city = $country = '';
                                      }else{
                                          if(isset($geoData->results[0])) {
                                              foreach($geoData->results[0]->address_components as $addressComponent) {
                                                  if(in_array('administrative_area_level_2', $addressComponent->types)) {
                                                      $city =  $addressComponent->long_name; 
                                                  }
                                                   if(in_array('country', $addressComponent->types)) {
                                                      $country =  $addressComponent->long_name; 
                                                  }
                                              }
                                          }else{
                                             $city = $country = '';
                                          }

                                    }
      
                 User::create([
                  'fullname' => $request->input('fullname'),
                  'username' => $request->input('username'),
                  'email' => $request->input('email'),
                  'password' => bcrypt($request->input('password')),
                  'lat'=>$request->input('lat'),
                  'lng'=>$request->input('lng'),
                  'profile_picture'=>$path,
                  'gender'=>$request->input('gender'),
                  'user_token'=>$user_token,
                  'city'=>$city,
                  'country'=>$country
                ]);
                 $profile= DB::table('tbl_user_register')
                                              ->selectRaw('fullname,email,lat,lng,profile_picture,username,city,country,last_active_door')
                                              ->where('id','=',Auth::user()->id)
                                              ->get();
                  return Response::json(array(
                    'error' => false,
                    'token'=>$user_token,
                    'user_profile'=>$profile,
                    'content' => 'User Registered'),
                    200
                );
            
    }
            
    }



    public function postRegisterFb(Request $request){
      $v = Validator::make($request->all(), [
                'fullname' => 'required|max:255',
               // 'username' =>'required|max:255',
                'fb_id' => 'required|max:255',
                'gender'=>'required',
                'profile_picture'=>'required',
                'lat'=>'required',
                'lng'=>'required'
        ]);

    if ($v->fails())
    {
        return Response::json(array(
            'error'=>true,
            'content'=>$v->errors()
            ),200);
    }else{

      $users  = User::where('fb_id','=',$request->input('fb_id'))->first();
       
      if(empty($users)){
        //register
          $user_token =  bcrypt(rand());
                 $url = 'http://maps.google.com/maps/api/geocode/json?latlng='.trim($request->input('lat')).','.trim($request->input('lng')).'&sensor=false'; 
                                      $get     = file_get_contents($url);
                                      $geoData = json_decode($get);
                                      if (json_last_error() !== JSON_ERROR_NONE) {
                                         $city = $country = '';
                                      }else{
                                          if(isset($geoData->results[0])) {
                                              foreach($geoData->results[0]->address_components as $addressComponent) {
                                                  if(in_array('administrative_area_level_2', $addressComponent->types)) {
                                                      $city =  $addressComponent->long_name; 
                                                  }
                                                   if(in_array('country', $addressComponent->types)) {
                                                      $country =  $addressComponent->long_name; 
                                                  }
                                              }
                                          }else{
                                             $city = $country = '';
                                          }

                                    }
      
                 User::create([
                  'fullname' => $request->input('fullname'),
                //  'username' => $request->input('username'),s
                  'fb_id' => $request->input('fb_id'),
                  'lat'=>$request->input('lat'),
                  'lng'=>$request->input('lng'),
                  'profile_picture'=>$request->input('profile_picture'),
                  'gender'=>$request->input('gender'),
                  'user_token'=>$user_token,
                  'city'=>$city,
                  'country'=>$country
                ]);
                    
                                 $profile= DB::table('tbl_user_register')
                                              ->selectRaw('fullname,email,lat,lng,profile_picture,username,fb_id,city,country,last_active_door')
                                              ->where('fb_id','=',$request->input('fb_id'))
                                              ->where('user_token','=',$user_token)
                                              ->get();
                  return Response::json(array(
                    'error' => false,
                    'token'=>$user_token,
                    'user_profile'=>$profile,
                    'content' => 'User Registered'),
                    200
                );
       
      }else{
        //login
        Auth::loginUsingId($users->id);

         $url = 'http://maps.google.com/maps/api/geocode/json?latlng='.trim($request->input('lat')).','.trim($request->input('lng')).'&sensor=false'; 
                                      $get     = file_get_contents($url);
                                      $geoData = json_decode($get);
                                      if (json_last_error() !== JSON_ERROR_NONE) {
                                         $city = $country = '';
                                      }else{
                                          if(isset($geoData->results[0])) {
                                              foreach($geoData->results[0]->address_components as $addressComponent) {
                                                  if(in_array('administrative_area_level_2', $addressComponent->types)) {
                                                      $city =  $addressComponent->long_name; 
                                                  }
                                                   if(in_array('country', $addressComponent->types)) {
                                                      $country =  $addressComponent->long_name; 
                                                  }
                                              }
                                          }else{
                                             $city = $country = '';
                                          }

                                    }

        $ress = DB::table('tbl_user_register')
                ->where('fb_id','=',$request->input('fb_id'))
                ->where('id','=',$users->id)
                ->update(['lat'=>$request->input('lat'),
                  'lng'=>$request->input('lng'),
                  'city'=>$city,
                  'country'=>$country
                  ]);

        $user_token =  $users->user_token;
       
                                 $profile= DB::table('tbl_user_register')
                                              ->selectRaw('fullname,email,lat,lng,profile_picture,username,fb_id,city,country,last_active_door')
                                              ->where('id','=',Auth::user()->id)
                                              ->get();

         return Response::json(array(
                    'error' => false,
                    'token'=>$user_token,
                    'user_profile'=>$profile,
                    'content' => 'Authentication True'),
                    200
                );
      }

    }
      
       


        
    }


    public function postLogin(Request $request){

        // Getting all post data
            $data = $request->all();

            // Applying validation rules.
            $rules = array(
                'email' => 'required|email',
                'password' => 'required',
                'lat'=>'required',
                'lng' =>'required'
                 );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()){
              // If validation falis redirect back to login.
             return Response::json(array(
                         'error'=>true,
                    'content'=>$validator->errors()
                    ),200);
            }
            else {
             
              // doing login.

                $userdata = array(
                    'email' => $request->input('email'),
                    'password' =>$request->input('password')
                  );
              // doing login.
                
              if (Auth::validate($userdata)) {
                 if (Auth::attempt($userdata)) {
                  // echo Auth::user()->id;
                  // exit;

                 
                                $url = 'http://maps.google.com/maps/api/geocode/json?latlng='.trim($request->input('lat')).','.trim($request->input('lng')).'&sensor=false'; 
                                      $get     = file_get_contents($url);
                                      $geoData = json_decode($get);
                                      if (json_last_error() !== JSON_ERROR_NONE) {
                                         $city = $country = '';
                                      }else{
                                          if(isset($geoData->results[0])) {
                                              foreach($geoData->results[0]->address_components as $addressComponent) {
                                                  if(in_array('administrative_area_level_2', $addressComponent->types)) {
                                                      $city =  $addressComponent->long_name; 
                                                  }
                                                   if(in_array('country', $addressComponent->types)) {
                                                      $country =  $addressComponent->long_name; 
                                                  }
                                              }
                                          }else{
                                             $city = $country = '';
                                          }

                                    }

                  $ress = DB::table('tbl_user_register')
                        ->where('id','=',Auth::user()->id)
                        ->update(['lat'=>$request->input('lat'),
                                'lng'=>$request->input('lng'),
                                'city'=>$city,
                                'country'=>$country
                              ]);

                        

                                $profile= DB::table('tbl_user_register')
                                              ->selectRaw('fullname,email,lat,lng,profile_picture,username,city,country,last_active_door')
                                              ->where('id','=',Auth::user()->id)
                                              ->get();

                    return Response::json(array(
                            'error'=>false,
                            'content'=>'Authtentication True',
                            'auth_check'=>1,
                            'user_profile'=>$profile,
                            'token'=>Auth::user()->user_token,
                            ),200);
                 }
              }else{
                return Response::json(array(
                            'error'=>true,
                            'content'=>'Authentication False...something Went Wrong',
                            'auth_check'=>0
                            ),200);
              }
                  
            }
        
    }

}
