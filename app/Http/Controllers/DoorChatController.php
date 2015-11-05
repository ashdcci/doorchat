<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Response;
use Redirect;
use DB;
use app\Event;
use Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Support\JsonableInterface;
use Illuminate\Contracts\Auth\Guard;
use App\User;
use Session;
use Hash;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Storage;
use Illuminate\Contracts\Filesystem\Factory;
use File;
//use Illuminate\Support\Facades\File;

class DoorchatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        //

        return Response::json(array(
            'error'=>true,
            'content'=>'User Not Authenticate to Access this Operation.'
            ),200);
    }   



    public function edit_user_profile(Request $request){
            $rules = [
            'lat'=>'required',
            'fullname'=>'required',
            'lng'=>'required',
            'token'=>'required'
            ];
               

            $v1 = Validator::make($request->all(),$rules);
            if($v1->fails()){
                return Response::json(array(
                    'error'=>false,
                    'content'=>$v1->errors()
                    ),200);
            }
             //$path = 'http://'.$_SERVER['HTTP_HOST'].'/public/uploads/user_profile/'.rand(11111,99999).'.'.$request->file('profile_picture')->getClientOriginalExtension();
           $path = '';
        $user = User::where('user_token', '=', $request->only('token'))->first();
        if(!empty($user)){
                   
            if($user->id>0){
                 $fullname  =  str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('fullname'));
         
                        if (!($request->hasFile('image'))) {
                            //profile pic not exist
                         
                              $res = DB::table('tbl_user_register')
                                        ->where('id','=',$user->id)
                                        ->update([
                                            'fullname'=>$fullname,
                                            'lat'=>$request->input('lat'),
                                            'lng'=>$request->input('lng')
                                            ]);   

                            // $res = DB::select(DB::raw('update tbl_user_register set fullname = "'.$fullname.'", lat ="'.$request->input('lat').'",
                            // lng = "'.$request->input('lng').'" where id ='.$user->id.'  '));
                 }else{
                     $ext = $request->file('image')->getClientOriginalExtension();
                     if(!($ext=='jpg' || $ext=='png' || $ext=='jpeg' || $ext=='gif' || $ext=='png')){
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'File Type not Supported',
                            'token'=>$request->input('token')
                            ),200);
                     }
                  
                    //delete file
                      File::delete('public/uploads/user_profile/'.$user->profile_picture);
                  
                      //move file
                    $destinationPath =   base_path() . '/public/uploads/user_profile/';
                    $fileName =rand(11111,99999).'.'.$request->file('image')->getClientOriginalExtension();
                    $fll =  $request->file('image')->move($destinationPath, $fileName);
                    $path = 'http://'.$_SERVER['HTTP_HOST'].'/projects/doorchat/public/uploads/user_profile/'.$fileName;
                    // update data

                    // $res = DB::select(DB::raw('update tbl_user_register set fullname = "'.$fullname.'", lat ="'.$request->input('lat').'",
                    //         lng = "'.$request->input('lng').'", profile_picture= "'.$path.'" where id ='.$user->id.'  '));    

                    $res = DB::table('tbl_user_register')
                            ->where('id','=',$user->id)
                            ->update([
                                'fullname'=>$fullname,
                                'lat'=>$request->input('lat'),
                                'lng'=>$request->input('lng'),
                                'profile_picture'=>$path
                                ]);   

                 }

                 return Response::json(array(
                    'error'=>false,
                    'content'=>'User Profile Updated',
                    'user_image_url'=>$path,
                    'token'=>$request->input('token')
                    ),200);
                 
            }
        }

            return Response::json(array(
                'error'=>true,
                'auth_check'=>0,
                'content'=>'User Not Authenticate'
                ),200);

    }


    public function get_user_profile(Request $request){

        $rules = [
        'token'=>'required'
        ];  

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        
        $user = User::where('user_token', '=', $request->only('token'))->first();

                if(!empty($user)){
                    if($user->id>0){
                    $res = Db::table('tbl_user_register')
                    ->select('fullname','username','lat','lng','profile_picture')
                    ->where('id','=',$user->id)
                    ->get();
                        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($user->lat).','.trim($user->lng).'&sensor=false';
                                      $json = @file_get_contents($url);
                                      $data=json_decode($json);
                                      $status = $data->status;
                                     // print_r($data);
                                      if($status=="OK"){
                                          $street  = $data->results[0]->address_components[0]->long_name;
                                          $colny  = $data->results[0]->address_components[1]->long_name;
                                          $city = $street  = $data->results[0]->address_components[2]->long_name;
                                          $country = $street  = $data->results[0]->address_components[5]->long_name;
                                      }else{
                                         $address =  $street = $colny = $city = $country = '';
                                      }


                    if(count($res)>0){
                         return Response::json(array(
                        'error'=>false,
                        'token'=>$request->input('token'),
                        'user_profile'=>$res,
                        'city'=>$city,
                        'country'=>$country
                        ),200);         
                    }
                     return Response::json(array(
                        'error'=>true,
                        'token'=>$request->input('token'),
                        'content'=>'Data not Fetched'
                        ),200);    
                    
                    }  
                }

       return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }


    public function create_door(Request $request){
        $rules = [
            'token'=>'required',
            'door_title'=>'required',
            'image'=>'required',
            'door_lat'=>'required',
            'door_lng'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
                
            if(!empty($user)){
                if($user->id>0){
         

                     if (!($request->hasFile('image'))) {
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'File Not Exist'
                            ),200);
                     }else{
                        $ext = $request->file('image')->getClientOriginalExtension();
                     if(!($ext=='jpg' || $ext=='png' || $ext=='jpeg' || $ext=='gif' || $ext=='png')){
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'File Type not Supported',
                            'token'=>$request->input('token')
                            ),200);

                     }
                        $destinationPath =   base_path() . '/public/uploads/door_uploads/';
                        $fileName =rand(11111,99999).'.'.$request->file('image')->getClientOriginalExtension();
                        $fll =  $request->file('image')->move($destinationPath, $fileName);
                        $path = 'http://'.$_SERVER['HTTP_HOST'].'/projects/doorchat/public/uploads/door_uploads/'.$fileName;
                     }

                    $res = Db::table('tbl_door')->insertGetId([
                        'door_title'=>$request->input('door_title'),
                        'door_image'=>$path,
                        'user_id'=>$user->id,
                        'door_lat'=>$request->input('door_lat'),
                        'door_lng'=>$request->input('door_lng'),
                        'last_active'=>date('Y-m-d H:i:s')
                        ]);
                  //  if($res>0){
                        return Response::json(array(
                        'error'=>false,
                        'token'=>$request->input('token'),
                        'content'=>'Door Generaterd',
                        'recent_door_id'=>$res
                        ),200);  
                    // }
                    // return Response::json(array(
                    //     'error'=>true,
                    //     'token'=>$request->input('token'),
                    //     'content'=>'Problam in Door Generation/Insert Error Occur'
                    //     ),200);  
                         
                }
                 return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }

       return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);


    }


    public function update_door_info(Request $request){
        $rules = [
        'door_id'=>'required',
        'token'=>'required',
        'door_title'=>'required',
        'door_lat'=>'required',
        'door_lng'=>'required'
        ];


        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

        $user = User::where('user_token', '=', $request->only('token'))->first();
                 $door_title = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('door_title'));
            if(!empty($user)){
                if($user->id>0){
                   if (!($request->hasFile('image'))) {
                   
                      // if door image not exist

                       // $res = DB::select(DB::raw('update tbl_door set door_title ="'.$door_title.'",door_lat = '.$request->input('door_lat').',
                       //  door_lng ='.$request->input('door_lng').' where id = '.$request->input('door_id').' and user_id = '.$request->input('user_id').' '));
                        $res = DB::table('tbl_door')
                                ->where('id','=',$request->input('door_id'))
                                ->where('user_id','=',$user->id)
                                ->update([
                                    'door_title'=>$door_title,
                                    'door_lat'=>$request->input('door_lat'),
                                    'door_lng'=>$request->input('door_lng')
                                    ]);

                     }else{
                        // door image exist
                        $ext = $request->file('image')->getClientOriginalExtension();
                     if(!($ext=='jpg' || $ext=='png' || $ext=='jpeg' || $ext=='gif' || $ext=='png')){
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'File Type not Supported',
                            'token'=>$request->input('token')
                            ),200);

                     }

                        // fetch old door image
                        $ress = DB::table('tbl_door')->select('id','door_image')->where('user_id','=',$user->id)->get();
                      
                        if(count($ress)>0){
                             
                            foreach($ress  as $val){
                                //echo $val->door_image;
                                File::delete('public/uploads/door_uploads/'.$val->door_image);
                            }

                        }

                        // upload new image and get file name
                        $destinationPath =   base_path() . '/public/uploads/door_uploads/';
                        $fileName =rand(11111,99999).'.'.$request->file('image')->getClientOriginalExtension();
                        $fll =  $request->file('image')->move($destinationPath, $fileName);
                        $path = 'http://'.$_SERVER['HTTP_HOST'].'/projects/doorchat/public/uploads/door_uploads/'.$fileName;

                        // now set data to be update
                        // $res = DB::select(DB::raw('update tbl_door set door_title ="'.$door_title.'",door_lat = '.$request->input('door_lat').',
                        // door_lng ='.$request->input('door_lng').',door_image = "'.$path.'" where id = '.$request->input('door_id').'
                        //  and user_id = '.$user->id.' '));

                         $res = DB::table('tbl_door')
                                ->where('id','=',$request->input('door_id'))
                                ->where('user_id','=',$user->id)
                                ->update([
                                    'door_title'=>$door_title,
                                    'door_lat'=>$request->input('door_lat'),
                                    'door_lng'=>$request->input('door_lng'),
                                    'door_image'=>$path
                                    ]);

                        
                     }

                    //  if($res>0){
                            return Response::json(array(
                            'error'=>false,
                            'content'=>'Door Info Updated',
                            'token'=>$request->input('token')
                            ),200);
                       //  }
                       // return Response::json(array(
                       //      'error'=>true,
                       //      'content'=>'Door Info Updated Error Occur',
                       //      'token'=>$request->input('token')
                       //      ),200);


                }
                return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }



    public function delete_door(Request $request){
        $rules = [
        'token'=>'required',
        'door_id'=>'required'
        ];
         $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    $res =  DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->where('user_id','=',$user->id)
                            ->delete();

                            if($res>0){
                                return Response::json(array(
                                'error'=>false,
                                'content'=>'Door Deleted',
                                'token'=>$request->input('token')
                                ),200);
                            }
                            return Response::json(array(
                                'error'=>true,
                                'content'=>'Problam in Door Deleting/Delete Error Occur',
                                'token'=>$request->input('token')
                                ),200);
                  }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    public function add_visited_door(Request $request){

         $rules = [
        'token'=>'required',
        'door_id'=>'required'
        ];
         $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    // find this door is belong to user id
                    $chk = DB::table('tbl_door')
                            ->where('user_id','<>',$user_id)
                            ->where('door_id','=',$request->input('door_id'))
                            ->first();
                    // if its belong then its yours

                            // else you can visit it.



                    $re1 = DB::table('tbl_door_visited')
                    ->where('visitor_id','=',$user->id)
                    ->where('door_id','=',$request->input('door_id'))
                    ->get();
                    if(count($re1)==0){

                        $res = DB::table('tbl_door_visited')->insertGetId([
                        'door_id'=>$request->input('door_id'),
                        'visitor_id'=>$user->id,
                        'visit_time'=>date('Y-m-d H:i:s')
                        ]);

                         $res1 = DB::table('tbl_door')
                                ->where('door_id','=',$request->input('door_id'))
                                ->update([
                                    'last_active'=>date('Y-m-d H:i:s')
                                    ]);
                             
                    }else{
                        
                        // $res = DB::select(DB::raw('update tbl_door_visited set visit_time = "'.date('Y-m-d H:i:s').'" where visitor_id = '.$user->id.' 
                        //     and door_id = '.$request->input('door_id').' '));
                         $res = DB::table('tbl_door_visited')
                                ->where('door_id','=',$request->input('door_id'))
                                ->where('visitor_id','=',$user->id)
                                ->update([
                                    'visit_time'=>date('Y-m-d H:i:s')
                                    ]);

                                 $res1 = DB::table('tbl_door')
                                ->where('door_id','=',$request->input('door_id'))
                                ->update([
                                    'last_active'=>date('Y-m-d H:i:s')
                                    ]);
                    }

                   // if($res>0){
                        return Response::json(array(
                                'error'=>false,
                                'content'=>'Door Visited',
                                'token'=>$request->input('token')
                                ),200);
                    // }
                    // return Response::json(array(
                    //             'error'=>true,
                    //             'content'=>'Error in Door Visiting/Data Insert Error occur',
                    //             'token'=>$request->input('token')
                    //             ),200);

                   
                  }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }


    public function delete_visited_door(Request $request){
         $rules = [
        'token'=>'required',
        'door_id'=>'required'
        ];
         $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                     $res =  DB::table('tbl_door_visited')
                            ->where('door_id','=',$request->input('door_id'))
                            ->where('visitor_id','=',$user->id)
                            ->delete();

                            //if($res>0){
                                return Response::json(array(
                                'error'=>false,
                                'content'=>'Visited Door Deleted',
                                'token'=>$request->input('token')
                                ),200);
                            // }
                            // return Response::json(array(
                            //     'error'=>true,
                            //     'content'=>'Problam in Deleting of Visited Door',
                            //     'token'=>$request->input('token')
                            //     ),200);
                            
                  }
                    return Response::json(array(
                            'error'=>true,
                            'auth_check'=>0,
                            'content'=>'User Not Authenticate'
                            ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }


    public function add_report_door(Request $request){
       $rules = [
        'token'=>'required',
        'door_id'=>'required',
        'report_title' =>'required',
        'report_desc'=>'required'
        ];
         $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    $report_title = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('report_title'));
                    $report_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('report_desc'));

                    $res = DB::table('tbl_door_report')->insertGetId([
                        'door_id'=>$request->input('door_id'),
                        'report_title'=>$report_title,
                        'report_desc'=>$report_desc
                        ]);

                  //  if($res>0){
                            return Response::json(array(
                                'error'=>false,
                                'content'=>'Report Generaterd to This Door',
                                'token'=>$request->input('token')
                                ),200);
                    // }
                    // return Response::json(array(
                    //             'error'=>true,
                    //             'content'=>'Error in Report Generating to This Door',
                    //             'token'=>$request->input('token')
                    //             ),200);        
                  }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }


    // Door Post Module
    public function add_door_post(Request $request){
        $rules = [
        'token'=>'required',
        'post_desc'=>'required',
        'door_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){

                  if (!($request->hasFile('image'))) {
                            //post image pic not exist
                         

                    $post_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('post_desc'));
                    $res = DB::table('tbl_door_post')->insertGetId([
                        'door_id'=>$request->input('door_id'),
                        'user_id'=>$user->id,
                        'post_desc'=>$post_desc
                        ]);

                    $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                            $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                                   

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
                    $destinationPath =   base_path() . '/public/uploads/post_image/';
                    $fileName =rand(11111,99999).'.'.$request->file('image')->getClientOriginalExtension();
                    $fll =  $request->file('image')->move($destinationPath, $fileName);
                    $path = 'http://'.$_SERVER['HTTP_HOST'].'/projects/doorchat/public/uploads/post_image/'.$fileName;
                   
                    $post_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('post_desc'));
                    $res = DB::table('tbl_door_post')->insertGetId([
                        'door_id'=>$request->input('door_id'),
                        'user_id'=>$user->id,
                        'post_image'=>$path,
                        'post_desc'=>$post_desc
                        ]);
                    $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                }

                   // if($res>0){
                        return Response::json(array(
                            'error'=>false,
                            'content'=>'Door Post Created',
                            'door_id'=>$request->input('door_id'),
                            'token'=>$request->input('token')
                        ),200);
                    // }
                    // return Response::json(array(
                    //         'error'=>true,
                    //         'content'=>'Error in Post Creation'
                    //     ),200);
                }

             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    public function delete_door_post(Request $request){
        $rules = [
        'token'=>'required',
        'post_id'=>'required',
        'door_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                     $res = DB::table('tbl_door_post')
                            ->where('user_id','=',$user->id)
                            ->where('id','=',$request->input('post_id'))
                            ->where('door_id','=',$request->input('door_id'))
                            ->delete();

                            $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                   // if($res>0){
                       return Response::json(array(
                        'error'=>false,
                        'content'=>'Post Deleted',
                        'token'=>$request->input('token')
                        ),200);
                    // }
                    // return Response::json(array(
                    // 'error'=>true,
                    // 'content'=>'Post Delete Error Occured',
                    // 'token'=>$request->input('token')
                    // ),200);
                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);

    }

    public function update_door_post(Request $request){
        $rules = [
        'token'=>'required',
        'id'=>'required',
        'door_id'=>'required',
        'post_desc'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                  $post_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('post_desc'));
                    if (!($request->hasFile('image'))) {
                            //post image pic not exist
                         

                    $post_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('post_desc'));
                    $res = DB::table('tbl_door_post as a')
                            ->leftJoin('tbl_door as b','a.door_id','=','b.id')
                            ->leftJoin('tbl_user_register as c','a.user_id','=','c.id')
                            ->where('a.door_id','=',$request->input('door_id'))
                            ->where('a.user_id','=',$user->id)
                            ->where('a.id','=',$request->input('id'))
                            ->update(['a.post_desc'=>$post_desc]);

                            $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                 }else{
                    $ext = $request->file('image')->getClientOriginalExtension();
                     if(!($ext=='jpg' || $ext=='png' || $ext=='jpeg' || $ext=='gif' || $ext=='png')){
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'File Type not Supported',
                            'token'=>$request->input('token')
                            ),200);

                     }
                     // fetch old post image
                     $ress = DB::table('tbl_door_post')
                                ->select('id','post_image')
                                ->where('user_id','=',$user->id)
                                ->where('id','=',$request->input('id'))
                                ->where('door_id',$request->input('door_id'))
                                ->get();
                      
                        if(count($ress)>0){
                             
                            foreach($ress  as $val){
                                //echo $val->door_image;
                                File::delete('public/uploads/door_uploads/'.$val->post_image);
                            }

                        }



                      //move file
                    $destinationPath =   base_path() . '/public/uploads/post_image/';
                    $fileName =rand(11111,99999).'.'.$request->file('image')->getClientOriginalExtension();
                    $fll =  $request->file('image')->move($destinationPath, $fileName);
                    $path = 'http://'.$_SERVER['HTTP_HOST'].'/projects/doorchat/public/uploads/post_image/'.$fileName;
                   
                    $post_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('post_desc'));
                     $res = DB::table('tbl_door_post as a')
                            ->leftJoin('tbl_door as b','a.door_id','=','b.id')
                            ->leftJoin('tbl_user_register as c','a.user_id','=','c.id')
                            ->where('a.door_id','=',$request->input('door_id'))
                            ->where('a.user_id','=',$user->id)
                            ->where('a.id','=',$request->input('id'))
                            ->update([
                                'a.post_desc'=>$post_desc,
                                'post_image'=>$path
                                ]);

                            $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                }




                


                  //  if($res > 0){
                       return Response::json(array(
                        'error'=>false,
                        'content'=>'Post Updated',
                        'token'=>$request->input('token')
                        ),200);
                    // }
                    // return Response::json(array(
                    // 'error'=>true,
                    // 'content'=>'Post Update Error Occured',
                    // 'token'=>$request->input('token')
                    // ),200);
                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    public function door_post_like(Request $request){
         $rules = [
        'token'=>'required',
        'id'=>'required',
        'door_id'=>'required',
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
        
                    $re1 = DB::table('tbl_door_post_likes')
                            ->select('*')
                            ->where('door_id','=',$request->input('door_id'))
                            ->where('post_id','=',$request->input('id'))
                            ->where('liker_id','=',$user->id)
                            ->get();
                           
                            if(count($re1)>0){
                                // then dislike and decr post like count
                                $r1 = DB::table('tbl_door_post_likes')
                                    ->where('liker_id','=',$user->id)
                                    ->where('post_id','=',$request->input('id'))
                                    ->where('door_id','=',$request->input('door_id'))
                                    ->delete();

                                 // $u1 = DB::select(DB::raw('update tbl_door_post set like_count = like_count-1 where door_id = '.$request->input('door_id').' and 
                                 // user_id = '.$user->id.' and id = '.$request->input('id').' and like_count>0 '));   
                                     $u1 = DB::table('tbl_door_post')
                                            ->where('door_id','=',$request->input('door_id'))
                                            ->where('user_id','=',$user->id)
                                            ->where('id','=',$request->input('id'))
                                            ->decrement('like_count');
                                 $u10 = DB::table('tbl_door')
                                        ->where('id','=',$request->input('door_id'))
                                        ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                          $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                               //  if($u1 > 0){
                                       return Response::json(array(
                                        'error'=>false,
                                        'content'=>'Post Unlike',
                                        'token'=>$request->input('token')
                                        ),200);
                                    // }
                                    // return Response::json(array(
                                    // 'error'=>true,
                                    // 'content'=>'Post Like Error Occured',
                                    // 'token'=>$request->input('token')
                                    // ),200);
                            }else{
                                // then like and incr post like count

                                $r1 = DB::table('tbl_door_post_likes')->insertGetId([
                                    'post_id'=>$request->input('id'),
                                    'liker_id'=>$user->id,
                                    'door_id'=>$request->input('door_id')
                                    ]);
                                 // $u1 = DB::select(DB::raw('update tbl_door_post set like_count = like_count+1 where door_id = '.$request->input('door_id').' and 
                                 // user_id = '.$user->id.' and id = '.$request->input('id').' '));

                                 $u1 = DB::table('tbl_door_post')
                                            ->where('door_id','=',$request->input('door_id'))
                                            ->where('user_id','=',$user->id)
                                            ->where('id','=',$request->input('id'))
                                            ->increment('like_count');

                                            $u10 = DB::table('tbl_door')
                                                        ->where('id','=',$request->input('door_id'))
                                                        ->update(['last_active'=>date('Y-m-d H:i:s')]);
                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                               //  if($u1 > 0){
                                       return Response::json(array(
                                        'error'=>false,
                                        'content'=>'Post Likes',
                                        'token'=>$request->input('token')
                                        ),200);
                                    // }
                                    // return Response::json(array(
                                    // 'error'=>true,
                                    // 'content'=>'Post Like Error Occured',
                                    // 'token'=>$request->input('token')
                                    // ),200);
                            }


                    
                    
                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    // fetch door post

    public function fetch_door_post(Request $request){
         $rules = [
        'token'=>'required',
        'door_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                      $perPage = 10;
                    $currentPage = !empty($request->input('page')) ? $request->input('page') : 1;
                    // $res = DB::select(DB::raw('select distinct a.id,a.door_id,a.post_desc,b.fullname,b.profile_picture from tbl_door_post a,tbl_user_register b,
                    //     tbl_door c where a.user_id = b.id and a.door_id = c.id order by a.id desc'));
                        $res =DB::table('tbl_door_post as a')
                                ->selectRaw('a.id,a.door_id,a.post_desc,b.fullname,b.profile_picture,a.like_count,a.comment_count')
                                ->leftJoin('tbl_user_register as b','a.user_id', '=', 'b.id')
                                ->leftJoin('tbl_door as c','a.door_id', '=', 'c.id')
                                ->orderBy('a.id', 'DESC')
                                ->where('a.door_id', $request->input('door_id'))
                                ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                                $u10 = DB::table('tbl_door')
                                    ->where('id','=',$request->input('door_id'))
                                    ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                      $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                     if(count($res)>0){
                        return Response::json(array(
                            'error'=>false,
                            'total_post'=>count($res),
                            'content'=>$res,
                            'token'=>$request->input('token')
                            ),200);
                    }
                    return Response::json(array(
                        'error'=>true,
                        'content'=>'Door Post Fetch Error Occured',
                         'token'=>$request->input('token')
                        ),200);
                }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }


    // End Door Post Module

    // Door Post Comment Module

    public function add_door_post_comment(Request $request){

         $rules = [
        'token'=>'required',
        'comment_desc'=>'required',
        'door_id'=>'required',
        'post_id'=>'required',
        'p_comment_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }
        $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    $comment_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('comment_desc'));
                    // fetch where this is a first post 
                   
                            if($request->input('p_comment_id')>0){
                              
                                // not a first post

                                $pc = DB::table('tbl_door_post_comment')
                                           ->where('door_id', $request->input('door_id'))
                                           ->where('post_id', $request->input('post_id'))
                                           ->where('id', $request->input('p_comment_id'))
                                           ->get();
                                          
                                           if(count($pc)>0){
                                            // pcomment id really related to this post and door id
                                            $res = DB::table('tbl_door_post_comment')->insertGetId([
                                                     'door_id'=>$request->input('door_id'),
                                                        'commenter_id'=>$user->id,
                                                        'post_comment_desc'=>$comment_desc,
                                                        'post_id'=>$request->input('post_id'),
                                                        'parent_comment_id'=>$request->input('p_comment_id')
                                                    ]);
                                            // update comment_count to its parent id
                                            $u1 = DB::table('tbl_door_post_comment')
                                                    ->where('door_id', $request->input('door_id'))
                                                    ->where('post_id', $request->input('post_id'))
                                                    ->where('id', $request->input('p_comment_id'))
                                                    ->increment('comment_count');



                                                    $u10 = DB::table('tbl_door')
                                                            ->where('id','=',$request->input('door_id'))
                                                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                                           }else{
                                            // p comment id not relate to this post and door
                                             return Response::json(array(
                                                    'error'=>true,
                                                    'content'=>'Parent Comment id not belong to this Door and post'
                                                ),200);
                                           }

                            }else{
                                // first post
                                 
                                    $res11 = DB::table('tbl_door_post_comment')->insertGetId([
                                        'door_id'=>$request->input('door_id'),
                                        'commenter_id'=>$user->id,
                                        'post_comment_desc'=>$comment_desc,
                                        'post_id'=>$request->input('post_id'),
                                        'parent_comment_id'=>'0'
                                        ]);

                                    // update comment_count to original post here
                                    $u1 = DB::table('tbl_door_post')
                                                    ->where('door_id', $request->input('door_id'))
                                                    ->where('user_id', $user->id)
                                                    ->where('id', $request->input('post_id'))
                                                    ->increment('comment_count');

                                                    $u10 = DB::table('tbl_door')
                                                            ->where('id','=',$request->input('door_id'))
                                                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                            $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                            }

                    
                   // if($res>0){
                        return Response::json(array(
                            'error'=>false,
                            'content'=>'Door Post Comment Created',
                            'door_id'=>$request->input('door_id'),
                            'token'=>$request->input('token')
                        ),200);
                    // }
                    // return Response::json(array(
                    //         'error'=>true,
                    //         'content'=>'Error in Post Comment Creation'
                    //     ),200);
                }

             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    public function update_door_post_comment(Request $request){
           $rules = [
        'token'=>'required',
        'comment_desc'=>'required',
        'door_id'=>'required',
        'post_id'=>'required',
        'comment_id'=>'required',
        'p_comment_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                  $comment_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('comment_desc'));
                    
                 
                    $res = DB::table('tbl_door_post_comment as a')
                            ->leftJoin('tbl_door_post as b','a.post_id','=','b.id')
                            ->leftJoin('tbl_door as c','a.door_id','=','c.id')
                            ->leftJoin('tbl_user_register as d','a.commenter_id','=','d.id')
                            ->where('a.door_id','=',$request->input('door_id'))
                            ->where('a.post_id','=',$request->input('post_id'))
                            ->where('a.parent_comment_id','=',$request->input('p_comment_id'))
                            ->where('a.id','=',$request->input('comment_id'))
                            ->update(['a.post_comment_desc'=>$comment_desc]);

                            $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                   // if($res > 0){
                       return Response::json(array(
                        'error'=>false,
                        'content'=>'Post Comment Updated',
                        'token'=>$request->input('token')
                        ),200);
                    // }
                    // return Response::json(array(
                    // 'error'=>true,
                    // 'content'=>'Post Comment Update Error Occured',
                    // 'token'=>$request->input('token')
                    // ),200);
                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    public function delete_door_post_comment(Request $request){
          $rules = [
        'token'=>'required',
        'door_id'=>'required',
        'post_id'=>'required',
        'comment_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                  $comment_desc = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $request->input('comment_desc'));
                    $res1 = DB::table('tbl_door_post_comment')
                            ->selectRaw('id,parent_comment_id')
                            ->where('post_id','=',$request->input('post_id'))
                            ->where('door_id','=',$request->input('door_id'))
                            ->where('commenter_id','=',$user->id)
                            ->where('id','=',$request->input('comment_id'))
                            ->first();
                           
                    if($res1->parent_comment_id >0){
                        // decrease comment count from its parent comment id in tbl_door_post_comment

                        $res = DB::table('tbl_door_post_comment')
                                ->where('id','=',$request->input('comment_id'))
                                ->where('post_id','=',$request->input('post_id'))
                                ->where('door_id','=',$request->input('door_id'))
                                ->where('commenter_id','=',$user->id)
                                ->delete();

                                $des = DB::table('tbl_door_post_comment')
                                        ->where('id','=',$res1->parent_comment_id)
                                        ->where('post_id','=',$request->input('post_id'))
                                        ->where('door_id','=',$request->input('door_id'))
                                        ->where('comment_count','>',0)
                                        ->decrement('comment_count');

                            $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                        //  $res = DB::select(DB::raw('delete from tbl_door_post_comment where id = '.$request->input('comment_id').' 
                        // and post_id = '.$request->input('post_id').' and door_id = '.$request->input('door_id').' and commenter_id ='.$user->id.' '));

                        //  $des = DB::select(DB::raw('update tbl_door_post_comment set comment_count = comment_count-1 where id = '.$res1->parent_comment_id.' 
                        // and post_id = '.$request->input('post_id').' and door_id = '.$request->input('door_id').' '));
                    }else{

                        // decrease parent comment count from tbl_door_post

                         $res = DB::table('tbl_door_post_comment')
                                ->where('id','=',$request->input('comment_id'))
                                ->where('post_id','=',$request->input('post_id'))
                                ->where('door_id','=',$request->input('door_id'))
                                ->where('commenter_id','=',$user->id)
                                ->where('parent_comment_id','=',$res1->id)
                                ->delete();

                                $des = DB::table('tbl_door_post')
                                        ->where('id','=',$request->input('post_id'))
                                        ->where('door_id','=',$request->input('door_id'))
                                        ->decrement('comment_count');

                         $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                        // $res = DB::select(DB::raw('delete from tbl_door_post_comment where id = '.$request->input('comment_id').' 
                        // and post_id = '.$request->input('post_id').' and door_id = '.$request->input('door_id').' and commenter_id ='.$user->id.' and parent_comment_id = '.$res1->id.' '));

                        // $des = DB::select(DB::raw('update tbl_door_post set comment_count  = comment_count - 1 where id ='.$request->input('post_id').'
                        //     and door_id = '.$request->input('door_id').' '));
                    }
                             
                   // if($res > 0){
                       return Response::json(array(
                        'error'=>false,
                        'content'=>'Post Comment Deleted',
                        'token'=>$request->input('token')
                        ),200);
                    // }
                    // return Response::json(array(
                    // 'error'=>true,
                    // 'content'=>'Post Comment Delete Error Occured',
                    // 'token'=>$request->input('token')
                    // ),200);
                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }



    public function door_post_comment_like(Request $request){
         $rules = [
        'token'=>'required',
        'id'=>'required',//comment id
        'door_id'=>'required',
        'post_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
        
                    $re1 = DB::table('tbl_door_post_comment_likes')
                            ->select('*')
                            ->where('door_id','=',$request->input('door_id'))
                            ->where('post_id','=',$request->input('post_id'))
                            ->where('liker_id','=',$user->id)
                            ->where('comment_id','=',$request->input('id'))
                            ->get();
                            if(count($re1)>0){
                                // then dislike and decr post comment like count
                                $r1 = DB::table('tbl_door_post_comment_likes')
                                    ->where('liker_id','=',$user->id)
                                    ->where('comment_id','=',$request->input('id'))
                                    ->where('post_id','=',$request->input('post_id'))
                                    ->where('door_id','=',$request->input('door_id'))
                                    ->delete();

                                     $u1 = DB::table('tbl_door_post_comment')
                                        ->where('post_id','=',$request->input('post_id'))
                                        ->where('door_id','=',$request->input('door_id'))
                                        ->where('commenter_id','=',$user->id)
                                        ->where('id','=',$request->input('id'))
                                        ->where('comment_like_count','>',0)
                                        ->decrement('comment_like_count');

                                        $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                  $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                                  // $u1 = DB::select(DB::raw('update tbl_door_post_comment set comment_like_count = comment_like_count-1 where door_id = '.$request->input('door_id').' and 
                                 // post_id = '.$request->input('post_id').' and commenter_id = '.$user->id.' and id = '.$request->input('id').' and comment_like_count>0 '));   
                                 // if($u1 > 0){
                                       return Response::json(array(
                                        'error'=>false,
                                        'content'=>'Post Comment Unliked',
                                        'token'=>$request->input('token')
                                        ),200);
                                    // }
                                    // return Response::json(array(
                                    // 'error'=>true,
                                    // 'content'=>'Post Comment Like Error Occured',
                                    // 'token'=>$request->input('token')
                                    // ),200);
                            }else{
                                // then like and incr post like count

                                $r1 = DB::table('tbl_door_post_comment_likes')->insertGetId([
                                    'post_id'=>$request->input('post_id'),
                                    'comment_id'=>$request->input('id'),
                                    'liker_id'=>$user->id,
                                    'door_id'=>$request->input('door_id')
                                    ]);
                                 // $u1 = DB::select(DB::raw('update tbl_door_post_comment set comment_like_count = comment_like_count+1 where door_id = '.$request->input('door_id').' and 
                                 // commenter_id = '.$user->id.' and post_id = '.$request->input('post_id').' and id = '.$request->input('id').' '));
                                 
                                 $u1 = DB::table('tbl_door_post_comment')
                                        ->where('post_id','=',$request->input('post_id'))
                                        ->where('door_id','=',$request->input('door_id'))
                                        ->where('commenter_id','=',$user->id)
                                        ->where('id','=',$request->input('id'))
                                        ->increment('comment_like_count');


                                        $u10 = DB::table('tbl_door')
                                                ->where('id','=',$request->input('door_id'))
                                                ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                  $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                                // if($u1 > 0){
                                       return Response::json(array(
                                        'error'=>false,
                                        'content'=>'Post Comment Liked',
                                        'token'=>$request->input('token')
                                        ),200);
                                    // }
                                    // return Response::json(array(
                                    // 'error'=>true,
                                    // 'content'=>'Post Like Error Occured',
                                    // 'token'=>$request->input('token')
                                    // ),200);
                            }


                    
                    
                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    //fetch_door_post_comment

    public function fetch_door_post_comment(Request $request){
         $rules = [
        'token'=>'required',
        'door_id'=>'required',
        'post_id'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    $perPage = 10;
                    $currentPage = !empty($request->input('page')) ? $request->input('page') : 1;

                    if($request->has('p_comment_id')){
                        // means get inner comment form particular comments
                             $res = DB::table('tbl_door_post_comment as a')
                                ->selectRaw('distinct a.id,a.parent_comment_id,a.post_comment_desc,c.fullname,c.profile_picture,a.comment_like_count,a.comment_count,
                                   case  when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>86400  then
                                        COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/86400,""),"d"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>3600  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/3600,""),"h"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,0),"")>1  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,""),"m"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),""),"s"),0) 
                                     end as created_time,
                                    case when e.id >0 then 1 else 0 end as is_like')
                                ->join('tbl_door_post as b','a.post_id','=','b.id')
                                ->join('tbl_user_register as c','a.commenter_id','=','c.id')
                                ->join('tbl_door as d','a.door_id','=','d.id')
                                ->leftJoin('tbl_door_post_comment_likes as e','a.id','=','e.comment_id')
                                ->where('a.door_id','=',$request->input('door_id'))
                                ->where('a.post_id','=',$request->input('post_id'))
                                ->where('a.parent_comment_id','=',$request->input('p_comment_id'))
                                ->orderBy('a.id','DESC')
                                ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                                $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                    }else{
                        // means get all parent comment
                        $res = DB::table('tbl_door_post_comment as a')
                                ->selectRaw('distinct a.id,a.parent_comment_id,a.post_comment_desc,c.fullname,c.profile_picture,a.comment_like_count,a.comment_count,
                                   case  when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>86400  then
                                        COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/86400,""),"d"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>3600  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/3600,""),"h"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,0),"")>1  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,""),"m"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),""),"s"),0) 
                                     end as created_time,
                                    case when e.id > 0 then 1 else 0 end as is_like')
                                ->join('tbl_door_post as b','a.post_id','=','b.id')
                                ->join('tbl_user_register as c','a.commenter_id','=','c.id')
                                ->join('tbl_door as d','a.door_id','=','d.id')
                                ->leftJoin('tbl_door_post_comment_likes as e','a.id','=','e.comment_id')
                                ->where('a.door_id','=',$request->input('door_id'))
                                ->where('a.post_id','=',$request->input('post_id'))
                                ->where('a.parent_comment_id','0')
                                ->orderBy('a.id','DESC')
                                ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                                $u10 = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);


                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                    }

                    return Response::json(array(
                        'error'=>false,
                        'total_comment'=>count($res),
                        'content'=>$res
                        ),200);

                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }





    // End door post comment module



    // fetch my door screen

    public function my_door_screen(Request $request){
         $rules = [
        'token'=>'required'
        ];
        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    // my doors
                    $profile []= array('fullname'=>$user->fullname,'profile_picture'=>$user->profile_picture);
                    $mydoor = DB::select(DB::raw('select id,door_title,door_image from tbl_door where user_id = '.$user->id.' order by id limit 0,1 '));

                    //neighbourhood doors
                   
                    $ndoor = DB::select(DB::raw('select id,door_title,door_image
 from tbl_door where user_id <> '.$user->id.' and round(TRUNCATE(( 6763 * acos( cos( radians( '.$user->lat.' ) ) * cos( radians( door_lat ) ) * cos( radians( door_lng ) - radians( '.$user->lng.' ) ) + sin( radians( '.$user->lat.' ) ) * sin( radians( door_lat ) ) ) ),2)) < 5  order by id desc limit 0,2 '));

                    
                    return Response::json(array(
                        'error'=>false,
                        'content'=>'data Fetched',
                        'my_profile'=>$profile,
                        'myDoor'=>$mydoor,
                        'neighbour'=>$ndoor,
                        'token'=>$request->input('token')
                        ),200);
                }
                
           return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

    public function manage_door_list(Request $request){
         $rules = [
        'token'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
         if($v->fails()){
             return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
        }

         $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    $perPage = 10;
                    $currentPage = !empty($request->input('page')) ? $request->input('page') : 1;
                    $myCreatedDoors = DB::table('tbl_door as a')
                                        ->selectRaw('a.id,a.door_title,a.door_image,a.door_total_member,
                                             case 

                                     when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,0),"")>60  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/3600,"")," Hours Ago"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,"")," Mins Ago"),0) 
                                     end as last_active,
                                     case 
                                        when round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2)) < 1
                                     then 
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2),2)," mi away") 
                                    else
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2))," mi away") 
                                    end as door_distance ')
                                        ->join('tbl_user_register as b','a.user_id','=','b.id')
                                        ->where('a.user_id','=',$user->id)
                                        ->orderBy('a.id','DESC')
                                        ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                    $myVisitedDoors = DB::table('tbl_door_visited as a')
                                        ->selectRaw('b.id,b.door_title,b.door_image,b.door_total_member, case 
                                     when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",b.last_active ))/60,0),"")>60  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",b.last_active ))/3600,"")," Hours Ago"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",b.last_active ))/60,"")," Mins Ago"),0) 
                                     end as last_active,
                                     case 
                                        when round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( b.door_lat ) ) * cos( radians( b.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( b.door_lat ) ) ) ),2)) < 1
                                     then 
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( b.door_lat ) ) * cos( radians( b.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( b.door_lat ) ) ) ),2),2)," mi away") 
                                    else
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( b.door_lat ) ) * cos( radians( b.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( b.door_lat ) ) ) ),2))," mi away") 
                                    end as door_distance')
                                        ->join('tbl_door as b','a.door_id','=','b.id')
                                        ->join('tbl_user_register as c','a.visitor_id','=','c.id')
                                        ->where('a.visitor_id','=',$user->id)
                                        ->orderBy('a.id','DESC')
                                        ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                    return Response::json(array(
                        'error'=>false,
                        'content'=>'Data Fetched',
                        'total_mycreated'=>count($myCreatedDoors),
                        'total_visited'=>count($myVisitedDoors),
                        'myCreatedDoors'=>$myCreatedDoors,
                        'myVisitedDoors'=>$myVisitedDoors,
                        'token'=>$request->input('token')
                        ),200);

                }
             return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
    }

public function fetch_mydoor_list(Request $request){
    $rules = [
    'token'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }

       $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                  //  echo $user->id;
                    // fetch which door that are created by me and also visited by me
                    // created by me =>tbl_door.user_id = tbl_user_register.id
                    // visited by me => tbl_door.id = tbl_door_visited.door_id and tbl_door.user_id <> tbl_door_visited.visitor_id 
                    
                    $perPage = 10;
                    $currentPage = !empty($request->input('page')) ? $request->input('page') : 1;

                    /*
                        0-all/ (actually random )
                        1-most recent created
                        2 - most visited
                        3- most interactive
                    */
                    if(!($request->has('order')) ){
                            $order_str = 'a.id';
                         }else{

                    if($request->input('order')=='0'){
                        //  0  = all (random)
                        $join_str = '';
                        $order_str ='a.id';
                    }else if($request->input('order')=='1'){
                        // 1 = most recent
                        $join_str = '';
                        $order_str = 'a.created_at';
                    }else if($request->input('order')=='2'){ 
                        // 2 = most visited
                        $join_str = '';
                        $order_str = 'a.visit_count';
                    }else{
                        // 3 = most interactive
                        $order_str = 'd.comment_count';
                         $res = DB::table('tbl_door as a')
                            ->selectRaw('a.id,a.user_id,a.door_title,a.door_image,a.door_total_member,
                                case 
                                     when COALESCE(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,0),"")>60  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/3600,"")," Hours Ago"),0)
                                     else
                                         COALESCE(concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,"")," Mins Ago"),0) 
                                     end as last_active
                                      ')
                            ->leftJoin('tbl_door_visited as b','a.id','=','b.door_id')
                            ->leftJoin('tbl_user_register as c','a.user_id','=','c.id')
                            ->leftJoin('tbl_door_post as d','a.id','=','d.door_id')
                            ->whereRaw('a.user_id = '.$user->id.'  or 
                                    (case when 
                                    a.id=b.door_id and a.user_id=b.visitor_id='.$user->id.'
                                    then a.user_id<>b.visitor_id 
                                    else a.user_id=b.visitor_id 
                                   end
                                    )')
                            ->orderBy($order_str,'DESC')
                            ->skip(($currentPage-1) * $perPage)->take($perPage)->get();


                            return Response::json(array(
                                'error'=>false,
                                'total_mydoor'=>count($res),
                                'content'=>$res
                                ),200);
                    }

                }
                    $res = DB::table('tbl_door as a')
                            ->selectRaw('a.id,a.user_id,a.door_title,a.door_image,a.door_total_member,
                                case 
                                     when COALESCE(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,0),"")>60  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/3600,"")," Hours Ago"),0)
                                     else
                                         COALESCE(concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,"")," Mins Ago"),0) 
                                     end as last_active
                                      ')
                            ->leftJoin('tbl_door_visited as b','a.id','=','b.door_id')
                            ->leftJoin('tbl_user_register as c','a.user_id','=','c.id')
                            ->whereRaw('a.user_id = '.$user->id.'  or 
                                    (case when 
                                    a.id=b.door_id and a.user_id=b.visitor_id='.$user->id.'
                                    then a.user_id<>b.visitor_id 
                                    else a.user_id=b.visitor_id 
                                   end
                                    )')
                            ->orderBy('a.id','DESC')
                            ->orderBy('b.id','DESC')
                            ->skip(($currentPage-1) * $perPage)->take($perPage)->get();


                    return Response::json(array(
                        'error'=>false,
                        'total_mydoor'=>count($res),
                        'content'=>$res
                        ),200);
                }
           return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
}



public function fetch_neighbour_list(Request $request){
    $rules = [
    'token'=>'required',
    'my_lat'=>'required',
    'my_lng'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }
//echo date('Y-m-d H:i:s');
       $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                  //  echo $user->id;
                    // fetch which door that are created by me and also visited by me
                    // created by me =>tbl_door.user_id = tbl_user_register.id
                    // visited by me => tbl_door.id = tbl_door_visited.door_id and tbl_door.user_id <> tbl_door_visited.visitor_id 
                     $perPage = 10;
                    $currentPage = !empty($request->input('page')) ? $request->input('page') : 1;
                    // for order 
                    /*
                        0-all/ (actually most recent )
                        1-most recent
                        2 - most visited
                        3- most interactive
                    */
                         if(!($request->has('order')) ){
                            $order_str = 'a.id';
                         }else{

                    if($request->input('order')=='0'){
                        //  0  = all (random)
                        $join_str = '';
                        $order_str ='a.id';
                    }else if($request->input('order')=='1'){
                        // 1 = most recent
                        $join_str = '';
                        $order_str = 'a.created_at';
                    }else if($request->input('order')=='2'){ 
                        // 2 = most visited
                        $join_str = '';
                        $order_str = 'a.visit_count';
                    }else{
                        // 3 = most interactive

                        $join_str = 'tbl_door_post';
                        $order_str = 'c.comment_count';
                        $res = DB::table('tbl_door as a')
                            ->selectRaw('a.id,a.user_id,a.door_title,a.door_image,a.door_total_member,
                                case 
                                     when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,0),"")>60  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/3600,"")," Hours Ago"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,"")," Mins Ago"),0) 
                                     end as last_active,
                                     case 
                                        when round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2)) < 1
                                     then 
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2),2)," mi away") 
                                    else
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2))," mi away") 
                                    end as door_distance  

                                     ') 
                        
                            ->leftJoin('tbl_user_register as b','a.user_id','=','b.id')
                            ->leftJoin('tbl_door_post as c','a.id','=','c.door_id')
                            ->whereRaw('a.user_id <> '.$user->id.' and round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2)) < 5')
                            ->orderBy($order_str,'DESC')
                            ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                            return Response::json(array(
                                'error'=>false,
                                'total_neighbour'=>count($res),
                                'content'=>$res
                                ),200);

                    }
                }
                  
                   

                    $res = DB::table('tbl_door as a')
                            ->selectRaw('a.id,a.user_id,a.door_title,a.door_image,a.door_total_member,
                                case 
                                     when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,0),"")>60  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/3600,"")," Hours Ago"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.last_active ))/60,"")," mins Ago"),0) 
                                     end as last_active,
                                     case 
                                        when round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2)) < 1
                                     then 
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2),2)," mi away") 
                                    else
                                        concat(round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2))," mi away") 
                                    end as door_distance  
                                     ') 
                            ->leftJoin('tbl_user_register as b','a.user_id','=','b.id')
                            ->whereRaw('a.user_id <> '.$user->id.' and round(TRUNCATE(( 3961 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2)) < 5')
                            ->orderBy($order_str,'DESC')
                            ->skip(($currentPage-1) * $perPage)->take($perPage)->get();
                          // echo count($res);

                    return Response::json(array(
                        'error'=>false,
                        'total_neighbour'=>count($res),
                        'content'=>$res
                        ),200);
                }
           return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
}
//round(TRUNCATE(( 6763 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( a.door_lat ) ) * cos( radians( a.door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( a.door_lat ) ) ) ),2)) as door_distance

public function fetch_door_single(Request $request){

    $rules = [
    'token'=>'required',
    'door_id'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }

       $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    
                    $fetch = DB::table('tbl_door')
                                ->selectRaw('id,door_lat,door_lng,door_title,door_image,user_id')
                                ->where('id','=',$request->input('door_id'))
                                ->first();
                        
                                if(count($fetch)>0){
                                    $perPage = 10;
                                    $currentPage = !empty($request->input('page')) ? $request->input('page') : 1;

                                $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($fetch->door_lat).','.trim($fetch->door_lng).'&sensor=false';
                                $get     = file_get_contents($url);
                                      $geoData = json_decode($get);
                               // print_r($data);
                                      $address = $city = $country = $street = $code = $colny =  '';
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                         $city = $country = '';
                                      }else{
                                          if(isset($geoData->results[0])) {
                                              foreach($geoData->results[0]->address_components as $addressComponent) {
                                                    if(in_array('sublocality_level_2', $addressComponent->types)) {
                                                      $street .=  $addressComponent->long_name; 
                                                    }
                                                     if(in_array('sublocality_level_1', $addressComponent->types)) {
                                                      $colny .=  $addressComponent->long_name; 
                                                    }

                                                  if(in_array('administrative_area_level_2', $addressComponent->types)) {
                                                      $city .=  $addressComponent->long_name; 
                                                  }
                                                  if(in_array('postal_code', $addressComponent->types)) {
                                                      $code .=  $addressComponent->long_name; 
                                                  }
                                                   if(in_array('country', $addressComponent->types)) {
                                                      $country .=  $addressComponent->long_name; 
                                                  }
                                              }
                                             $address = $street.' '.$colny.' '.$city.' '.$code;
                                          }else{
                                            $address = $city = $country = $street = $code = '';
                                          }

                                    }
                                // if($status=="OK"){
                                //     $street  = $data->results[0]->address_components[0]->long_name;
                                //     $colny  = $data->results[0]->address_components[1]->long_name;
                                //     $city = $street  = $data->results[0]->address_components[2]->long_name;
                                //     $code = $street  = $data->results[0]->address_components[5]->long_name;
                                //     $address = $street.' '.$colny.' '.$city.' '.$code;
                                // }else{
                                //    $address =  $street = $colny = $city = $code = '';
                                // }
            
                                if($fetch->user_id==$user->id){
                                     // mydoor
                                     // $res = DB::select(DB::raw('select a.* from tbl_door_post a,tbl_door b,tbl_user_register c where
                                     //    a.door_id = b.id and b.user_id =c.id and b.user_id = c.id and a.door_id = '.$request->input('door_id').'
                                     //     and b.user_id = '.$user->id.' order by desc '));

                                    $res = DB::table('tbl_door_post as a')
                                            ->selectRaw('a.*,c.fullname,c.profile_picture,c.username, case 
                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>86400  then
                                        COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/86400,""),"d"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>3600  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/3600,""),"h"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,0),"")>1  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,""),"m"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),""),"s"),0) 
                                     end as created_time
                                     ,
                                     case when d.id >0 then 1 else 0 end as is_like
                                     ')
                                            ->join('tbl_door as b','a.door_id','=','b.id')
                                            ->join('tbl_user_register as c','b.user_id','=','c.id')
                                            ->leftJoin('tbl_door_post_likes as d','a.id','=','d.post_id')
                                            ->whereRaw('a.door_id = '.$request->input('door_id').'  and b.user_id = '.$user->id.' ')
                                            ->orderBy('a.id','DESC')
                                            ->skip(($currentPage-1) * $perPage)->take($perPage)->get();
                                    $u10 = DB::table('tbl_door')
                                        ->where('id','=',$request->input('door_id'))
                                        ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                          $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                                }else{
                                    // neighbour door
                                     // $res = DB::select(DB::raw('select a.* from tbl_door_post a,tbl_door b,tbl_user_register c where
                                     //    a.door_id = b.id and b.user_id =c.id and b.user_id = c.id and a.door_id = '.$request->input('door_id').' '));

                                     $res = DB::table('tbl_door_post as a')
                                            ->selectRaw('a.*,c.fullname,c.profile_picture,c.username, case 
                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>86400  then
                                        COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/86400,""),"d"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),0),"")>3600  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/3600,""),"h"),0)

                                    when COALESCE( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,0),"")>1  then
                                         COALESCE(concat( round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at ))/60,""),"m"),0)
                                     else
                                         COALESCE( concat(round( TIME_TO_SEC(TIMEDIFF("'.date('Y-m-d H:i:s').'",a.created_at )),""),"s"),0) 
                                     end as created_time,
                                     case when d.id >0 then 1 else 0 end as is_like')
                                            ->join('tbl_door as b','a.door_id','=','b.id')
                                            ->join('tbl_user_register as c','b.user_id','=','c.id')
                                            ->leftJoin('tbl_door_post_likes as d','a.id','=','d.post_id')
                                            ->whereRaw('a.door_id = '.$request->input('door_id').'  ')
                                            ->orderBy('a.id','DESC')
                                            ->skip(($currentPage-1) * $perPage)->take($perPage)->get();

                                    $u10 = DB::table('tbl_door')
                                            ->where('id','=',$request->input('door_id'))
                                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);
                                }
                                return Response::json(array(
                                    'error'=>false,
                                    'total_post'=>count($res),
                                    'content'=>$res,
                                    'door_address'=>$address,
                                    'door_image'=>$fetch->door_image,
                                    'door_title'=>$fetch->door_title
                                    ),200);
                            }
                            return Response::json(array(
                                    'error'=>true,
                                    'content'=>'No Door Found',
                                    'token'=>$request->input('token')
                                    ),200);
                                                       
                }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
}


public function connect_to_door(Request $request){
    $rules = [
    'token'=>'required',
    'door_id'=>'required',
    'my_lat'=>'required',
    'my_lng'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }

       $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){
                    // return  $sql = 'select id,door_title,(round(TRUNCATE(( 6763 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( door_lat ) ) * cos( radians( door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( door_lat ) ) ) ),2))*1000) as distan
                    //    from tbl_door where id ='.$request->input('door_id').' and  (round(TRUNCATE(( 6763 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( door_lat ) ) * cos( radians( door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( door_lat ) ) ) ),2))*1000)>300 ';
                    //and round(TRUNCATE(( 6763 * acos( cos( radians( '.$user->lat.' ) ) * cos( radians( door_lat ) ) * cos( radians( door_lng ) - radians( '.$user->lng.' ) ) + sin( radians( '.$user->lat.' ) ) * sin( radians( door_lat ) ) ) ),2)) < 5
                
                    $sql ='select id,door_title,user_id,round(TRUNCATE(( 6763 * acos( cos( radians('.$request->input('my_lat').') ) * cos( radians( door_lat ) ) * cos( radians( door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( door_lat ) ) ) ),2)*1000,2) as distan
                     from tbl_door where id ='.$request->input('door_id').' and user_id<>'.$user->id.' and  round(TRUNCATE(( 6763 * acos( cos( radians( '.$request->input('my_lat').' ) ) * cos( radians( door_lat ) ) * cos( radians( door_lng ) - radians( '.$request->input('my_lng').' ) ) + sin( radians( '.$request->input('my_lat').' ) ) * sin( radians( door_lat ) ) ) ),2)*1000,2) <300';
                    // check if this door distance is less then 300 m radius to user latest radiuss   
                     $res = Db::select(DB::raw($sql));

                     if(count($res)>0){
                        foreach($res as $val){
                            $user_id = $val->user_id;
                             $dis = $val->distan;
                        }
                        
                        //'if distance matched then insert into tbl_door_visited and tbl_door_member';
                        
                        // check if data is already inserted
                       

                        $fetch = DB::select(DB::raw('select a.* from tbl_user_register a,tbl_door_visited b,tbl_door_members c
                         where a.id = b.visitor_id and a.id  = c.member_id and b.visitor_id = c.member_id and a.id = '.$user->id.' and b.door_id ='.$request->input('door_id').' 
                         and c.door_id = '.$request->input('door_id').' order by a.id desc limit 0,1'));
                        if(count($fetch)>0){
                            return Response::json(array(
                                'error'=>true,
                                'content'=>'already connected to this door',
                                'token'=>$request->input('token')
                                ),200);
                        }else{
                            // insert into tbl_door_visited
                            $ins = DB::table('tbl_door_visited')->insertGetId([
                            'door_id'=>$request->input('door_id'),
                            'visitor_id'=>$user->id
                            ]);

                            // insert into tbl_door_member
                            $ins = DB::table('tbl_door_members')->insertGetId([
                            'door_id'=>$request->input('door_id'),
                            'member_id'=>$user->id,
                            'user_id'=>$user_id,
                            ]);

                            $u10 = DB::table('tbl_door')
                                    ->where('id','=',$request->input('door_id'))
                                    ->where('user_id','=',$user_id)
                                    ->update(['last_active'=>date('Y-m-d H:i:s')]);

                                     $u101 = DB::table('tbl_door')
                                    ->where('id','=',$request->input('door_id'))
                                    ->where('user_id','=',$user_id)
                                    ->increment('visit_count')
                                    ->increment('door_total_members');

                                      $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);


                            return Response::json(array(
                                'error'=>false,
                                'content'=>'You Connected with this door',
                                'token'=>$request->input('token')
                                ),200);
                        }

                            
                        

                     }else{
                        return Response::json(array(
                            'error'=>true,
                            'content'=>'Distance Not Matched',
                            'token'=>$request->input('token'),
                            'sql'=>$sql
                            ),200);
                     }

                     


                }
           return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
}


public function share_post_timeline(Request $request){
    $rules = [
    'token'=>'required',
    'door_id'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }

       $user = User::where('user_token', '=', $request->only('token'))->first();
            if(!empty($user)){
                if($user->id>0){

                    $res = DB::table('tbl_door')
                            ->where('id','=',$request->input('door_id'))
                            ->update(['last_active'=>date('Y-m-d H:i:s')]);

                              $u11 = DB::table('tbl_user_register')
                                ->where('id','=',$user->id)
                                ->update(['last_active_door'=>$request->input('door_id')]);

                    return Response::json(array(
                        'error'=>false,
                        'content'=>'Data Updated After Share',
                        'token'=>$request->input('token')
                        ),200);
                }
                  return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);
            }
            return Response::json(array(
                    'error'=>true,
                    'auth_check'=>0,
                    'content'=>'User Not Authenticate'
                    ),200);   
}



public function delete_unused_door(Request $request){
    $res = DB::select(DB::raw('select distinct id from tbl_door where round(time_to_sec(timediff(now(),last_active))/86400) > 30 '));
    if(count($res)>0){
        foreach($res as $val){
            $del = DB::table('tbl_door')
                    ->where('id','=',$val->id)
                    ->delete();
        }
    }

    return;
}










/*
select distinct a.id,a.last_active from tbl_door as a 
left join tbl_user_register as f on a.user_id = f.id
left join tbl_door_post as b on a.id = b.door_id and f.id = b.user_id
left join tbl_door_post_comment as c on a.id = c.door_id and f.id = c.commenter_id
left join tbl_door_post_likes as d on a.id = d.door_id and f.id = d.liker_id
left join tbl_door_post_comment_likes as e on a.id  = e.door_id and f.id = e.liker_id
where f.id = 4
order by a.last_active desc
*/




    




















}
