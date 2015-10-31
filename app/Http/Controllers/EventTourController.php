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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class EventTourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function __construct(Request $request){
      
        if(Auth::attempt(['email_address'=>$request->input('email'),'password'=>$request->input('password')])){
        }
        return Response::make(array(
                    'error'=>true,
                    'content'=>'User Not Authenticate'
                    ),200);
    }



    public function index()
    {
        //
         return Response::make(array(
                    'error'=>true,
                    'content'=>'cant Use this Section'
                    ),200);
    }

        

     public function add_event(Request $request,User $users){
           


    // get post data and add event here
        $rules = [
        'competitive_level'=>'required',
        'league_level'=>'required',
        'public_status'=>'required',
        'city_name'=>'required',
        'zipcode'=>'required',
        'email'=>'required|email|max:255',
        'password'=>'required|min:6'
        ];

        $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
            return Response::make(array(
                    'error'=>true,
                    'content'=>$v1->errors()
                    ),200);
        }

       $res =  DB::table('tbl_event')->insertGetId(
            [
                'intUserId' => Auth::user()->intId,
                'competitive_level' => $request->input('competitive_level'),
                'league_level'=>$request->input('league_level'),
                'public_status'=>$request->input('public_status'),
                'city_name' => $request->input('city_name'),
                'zipcode' =>$request->input('zipcode')
            ]
        );
     return Response::make(array(
                    'error'=>false,
                    'content'=>'Event Created',
                    'intEventId'=>$res
                    ),200);

     
    }


    public function add_event_desc(Request $request){
      
            // get post data and add event here
                $rules = [
                    'intEventId'=>'required',
                    'tournament_name'=>'required',
                    'tournament_type'=>'required',
                    'team_number'=>'required',
                    'court_number'=>'required',
                    'password'=>'required|min:6',
                    'start_date' =>'required',
                    'end_date' =>'required'
                ];

               $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $id = DB::table('tbl_event_tournament')->insertGetId( [
                    'intUserId'=>Auth::user()->intId,
                    'intEventId' =>$request->input('intEventId'),
                    'tournament_name' =>$request->input('tournament_name'),
                    'tournament_type' => $request->input('tournament_type'),
                    'team_number' =>$request->input('team_number'),
                    'court_number' =>$request->input('court_number'),
                    'start_date' =>$request->input('start_date'),
                    'end_date' => $request->input('end_date')
                    ]);

                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Description Created',
                    'event_desc_id'=>$id,
                    'intUserId' =>Auth::user()->intId,
                    'intEventId'=>$request->input('intEventId')
                    ),200); 


        
    }

     // fetch all tournaments
    public function fetch_all_tournament(Request $request){
            $res = DB::select(DB::raw('select a.*,b.email_address,c.league_level from tbl_event_tournament a,tbl_user_register b,tbl_event c
             where a.intUserId = b.intId and a.intEventId  = c.intId'));
               return Response::json(array(
                    'error' => false,
                    'urls' => $res
                ),200);

    }


    // add team

    public function add_team(Request $request){

            $rules = [
            'email' =>'required|email|max:255',
            'password' => 'required',
            'team_name' =>'required',
            'file' =>'required',
            'team_color' =>'required',
            'intTourId' =>'required'
         ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>false,
                'content'=>$v->errors()
                ),200);
        }

        $destinationPath =   base_path() . '/public/uploads/';
             $file = $request->file('file');
            
             if (!($request->hasFile('file'))) {
                return Response::json(array(
                      'error' => true,
                      'response' =>'File Not Exist'
                      ),
                      200
                      );
               //
             

         }else{
              $fileName =rand(11111,99999).Auth::user()->intId.$request->input('file_ext');

             $fll =  $request->file('file')->move($destinationPath, $fileName);
         }
        

        $res = DB::table('tbl_event_tournament_team')->insertGetId([
            'team_name' =>$request->input('team_name'),
            'team_logo' =>$fileName,
            'team_color'=>$request->input('team_color'),
            'intTourId' =>$request->input('intTourId')
            ]);

        return Response::json(array(
            'error'=>false,
            'content'=>'Team Generaterd',
            'recent_team_id' =>$res,
            'intTourId' =>$request->input('intTourId'),
            'intUserId'=>Auth::user()->intId
            ),200);

       
    }



    public function add_player(Request $request){
      
                  $rules = [
                        'email' =>'required|email|max:255',
                        'password' => 'required',
                        'intTeamId' =>'required',
                        'player_name' =>'required',
                        'file' =>'required',
                        'intTourId' =>'required'
                     ];

                $v = Validator::make($request->all(),$rules);
                if($v->fails()){
                    return Response::json(array(
                        'error'=>false,
                        'content'=>$v->errors()
                        ),200);
                }

                 $destinationPath =   base_path() . '/public/uploads/';
                     //$file = $request->file('file');
                    
                     if (!($request->hasFile('file')) && !($request->hasFile('file1'))) {
                        return Response::json(array(
                              'error' => true,
                              'response' =>'File Not Exist'
                              ),
                              200
                              );
                       //
                     

                 }else{
                      $fileName =rand(11111,99999).Auth::user()->intId.$request->input('file_ext');

                     $fll =  $request->file('file')->move($destinationPath, $fileName);


                     // file two upload

                      $fileName1 =rand(11111,99999).Auth::user()->intId.$request->input('file_ext1');

                     $fll =  $request->file('file1')->move($destinationPath, $fileName1);
                 }
                

                $res = DB::table('tbl_event_tournament_team_players')->insertGetId([
                    'player_name' =>$request->input('player_name'),
                    'player_profile' =>$fileName,
                    'player_adv_image' =>$fileName1,
                    'intTeamId'=>$request->input('intTeamId'),
                    'intTourId' =>$request->input('intTourId')
                    ]);

                return Response::json(array(
                    'error'=>false,
                    'content'=>'Player Generaterd',
                    'recent_player_id' =>$res,
                    'intTourId' =>$request->input('intTourId'),
                    'intTeamId' =>$request->input('intTeamId'),
                    'intUserId'=>Auth::user()->intId
                    ),200);
    }

    // add tournament settings
    public function add_tour_settings(Request $request){
        
            // get post data and add event here
                $rules = [
                    'intTourId'=>'required',
                    'game_dates'=>'required',
                    'games_per_day'=>'required',
                    'games_per_court'=>'required',
                    'conversation_status'=>'required',
                    'auto_cancel'=>'required',
                    'cancel_type' =>'required',
                    'cancel_high_temp' =>'required',
                    'cancel_low_temp' =>'required'
                ];

               $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $id = DB::table('tbl_event_tournament_settings')->insertGetId( [
                    'intTourId' =>$request->input('intTourId'),
                    'game_dates' =>$request->input('game_dates'),
                    'games_per_day' => $request->input('games_per_day'),
                    'games_per_court' =>$request->input('games_per_court'),
                    'conversation_status' =>$request->input('conversation_status'),
                    'auto_cancel' =>$request->input('auto_cancel'),
                    'cancel_type' => $request->input('cancel_type'),
                    'cancel_high_temp' =>$request->input('cancel_high_temp'),
                    'cancel_low_temp'=>$request->input('cancel_low_temp')
                    ]);

                return Response::json(array(
                    'error'=>false,
                    'content'=>'Tournament Settings Created',
                    'tour_setting_id'=>$id,
                    'intUserId' =>Auth::user()->intId,
                    'intTourId'=>$request->input('intTourId')
                    ),200); 
    }


    public function fetch_single_tournament(Request $request){
        
            $res = DB::select(DB::raw('select a.*,b.* from tbl_event_tournament a,tbl_event_tournament_settings b
             where a.intId = b.intTourId and a.intId ='.$request->input('intTourId').' '));
               return Response::json(array(
                    'error' => false,
                    'urls' => $res
                ),200);
      
    }

    public function update_tournament_details(Request $request){
      
            // get post data and add event here
                $rules = [
                    'intTourId'=>'required',
                    'game_dates'=>'required',
                    'games_per_day'=>'required',
                    'games_per_court'=>'required',
                    'conversation_status'=>'required',
                    'auto_cancel'=>'required',
                    'cancel_type' =>'required',
                    'cancel_high_temp' =>'required',
                    'cancel_low_temp' =>'required'
                ];

               $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $res = DB::select(DB::raw("update tbl_event_tournament_settings set game_dates ='".$request->input('game_dates')."',
                    games_per_day = '".$request->input('games_per_day')."',games_per_court = '".$request->input('games_per_court')."',
                    conversation_status='".$request->input('conversation_status')."',auto_cancel = '".$request->input('auto_cancel')."',
                    cancel_type='".$request->input('cancel_type')."',cancel_high_temp = '".$request->input('cancel_high_temp')."',
                    cancel_low_temp='".$request->input('cancel_low_temp')."' where intTourId = ".$request->input('intTourId')." "));

                return Response::json(array(
                    'error'=>false,
                    'content'=>'Tournament Settings Updated',
                    'intUserId' =>Auth::user()->intId,
                    'intTourId'=>$request->input('intTourId')
                    ),200); 
    }

    public function update_tournament_info(Request $request){
            // get post data and add event here
                  $rules = [
                    'intEventId'=>'required',
                    'tournament_name'=>'required',
                    'tournament_type'=>'required',
                    'team_number'=>'required',
                    'court_number'=>'required',
                    'password'=>'required|min:6',
                    'start_date' =>'required',
                    'end_date' =>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $res = DB::select(DB::raw("update tbl_event_tournament set tournament_name ='".$request->input('tournament_name')."',
                    tournament_type = '".$request->input('tournament_type')."',team_number = '".$request->input('team_number')."',
                    court_number='".$request->input('court_number')."',start_date = '".$request->input('start_date')."',
                    end_date='".$request->input('end_date')."' where intEventId = ".$request->input('intEventId')." and intId = ".$request->input('intTourId')." "));

                return Response::json(array(
                    'error'=>false,
                    'content'=>'Tournament Info Updated',
                    'intUserId' =>Auth::user()->intId,
                    'intTourId'=>$request->input('intTourId')
                    ),200); 
    }

    public function delete_tournament(Request $request){
            $rules = [
                    'intEventId'=>'required',
                    'intId'=>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }
                $res = DB::select(DB::raw('delete from tbl_event_tournament where intId ='.$request->input('intId').' 
                    and intEventId = '.$request->input('intEventId').' '));
                
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Now Deleted'
                    ),200);
    }

    public function add_tour_game(Request $request){
       
            $rules = [
                    'intEventId'=>'required',
                    'intEventId'=>'required',
                    'intTourId'=>'required',
                    'intFirstTeamId' =>'required',
                    'intSecondTeamId' =>'required',
                    'court_number'=>'required',
                    'quater_number'=>'required',
                    'game_start_time' =>'required',
                    'game_end_time' =>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }
                $res = DB::table('tbl_event_tournament_game')->insertGetId([
                    'intEventId'=>$request->input('intEventId'),
                    'intTourId' =>$request->input('intTourId'),
                    'intFirstTeamId'=>$request->input('intFirstTeamId'),
                    'intSecondTeamId'=>$request->input('intSecondTeamId'),
                    'court_number'=>$request->input('court_number'),
                    'quater_number'=>$request->input('quater_number'),
                    'game_start_time'=>$request->input('game_start_time'),
                    'game_end_time'=>$request->input('game_end_time')
                    ]);
                
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Game Created',
                    'recent_game_id'=>$res,
                    'intEventId'=>$request->input('intEventId'),
                    'intTourId'=>$request->input('intTourId')
                    ),200);
    }

    public function delete_tour_game(Request $request){
         
            $rules = [
                    'intEventId'=>'required',
                    'intEventId'=>'required',
                    'intTourId'=>'required',
                    'intGameId' =>'required',
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }
                $res = DB::select(DB::raw('delete from tbl_event_tournament_game where intId ='.$request->input('intGameId').'
                 and intEventId ='.$request->input('intEventId').' and intTourId ='.$request->input('intTourId').'  '));

                
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Game Now Deleted',
                    'intEventId'=>$request->input('intEventId'),
                    'intTourId'=>$request->input('intTourId')
                    ),200);
    }

    public function add_event_tour_team_score(Request $request){
            $rules = [
                    'intGameId'=>'required',
                    'intTeamId'=>'required',
                    'Q1'=>'required',
                    'Q2' =>'required',
                    'Q3'=>'required',
                    'Q4'=>'required',
                    'total_score'=>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $r1 = DB::select(DB::raw(
                    'select intId from tbl_event_tournament_game_team_score where intGameId ='.$request->input('intGameId').' and intTeamId= '.$request->input('intTeamId').' '
                    ));
                $total_score = $request->input('Q1')+$request->input('Q2')+$request->input('Q3')+$request->input('Q4');

                if(count($r1)>0){
                    // updatre case
                        foreach($r1 as $val){
                        $res = DB::select(DB::raw(' update tbl_event_tournament_game_team_score set Q1 =Q1+ '.$request->input('Q1').',Q2=Q2+'.$request->input('Q2').',
                            Q3=Q3+'.$request->input('Q3').',Q4=Q4+'.$request->input('Q4').',total_score=total_score+'.$total_score.' where intGameId ='.$request->input('intGameId').'
                             and intTeamId= '.$request->input('intTeamId').' and intId = '.$val->intId.' '));
                         }
                }else{
                    // insert case
                    $res = DB::select(DB::raw('insert into tbl_event_tournament_game_team_score(intGameId,intTeamId,Q1,Q2,Q3,Q4,total_score)values
                        ('.$request->input('intGameId').','.$request->input('intTeamId').','.$request->input('Q1').','.$request->input('Q2').',
                            '.$request->input('Q3').','.$request->input('Q4').','.$total_score.')'));
                }
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Game Team Score Now Generated',
                    'intGameId'=>$request->input('intGameId'),
                    'intTeamId'=>$request->input('intTeamId')
                    ),200);
    }



    public function add_event_tour_team_score_stats(Request $request){
            $rules = [
                    'intGameId'=>'required',
                    'intTeamId'=>'required',
                    'intEventId'=>'required',
                    'intTourId'=>'required',
                    'AST'=>'required',
                    'RBD' =>'required',
                    'BLK'=>'required',
                    'STL'=>'required',
                    'total_points'=>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $r1 = DB::select(DB::raw(
                    'select intId from tbl_event_tournament_game_score_stats where intGameId ='.$request->input('intGameId').' and intTeamId= '.$request->input('intTeamId').' and 
                    intEventId = '.$request->input('intEventId').' and intTourId ='.$request->input('intTourId').' '
                    ));
 
                if(count($r1)>0){
                    // updatre case
                        foreach($r1 as $val){
                        $res = DB::select(DB::raw(' update tbl_event_tournament_game_score_stats set AST = AST+'.$request->input('AST').',RBD=RBD+'.$request->input('RBD').',
                            BLK=BLK+'.$request->input('BLK').',STL=STL+'.$request->input('STL').',total_points = total_points+'.$request->input('total_points').' where intGameId ='.$request->input('intGameId').'
                             and intTeamId= '.$request->input('intTeamId').' and intId = '.$val->intId.' and intEventId = '.$request->input('intEventId').' 
                             and intTourId ='.$request->input('intTourId').' '));
                    }
                }else{
                    // insert case
                    $res = DB::select(DB::raw('insert into tbl_event_tournament_game_score_stats(intGameId,intTeamId,intTourId,intEventId,AST,RBD,BLK,STL,total_points)values('.$request->input('intGameId').','.$request->input('intTeamId').','.$request->input('intTourId').','.$request->input('intEventId').','.$request->input('AST').','.$request->input('RBD').','.$request->input('BLK').','.$request->input('STL').','.$request->input('total_points').')'));
                }
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Game Score Stats Now Generated',
                    'intEventId'=>$request->input('intEventId'),
                    'intTourId'=>$request->input('intTourId'),
                     'intGameId'=>$request->input('intGameId'),
                    'intTeamId'=>$request->input('intTeamId')
                    ),200);
       
    }



     public function add_event_tour_game_player_score(Request $request){
            $rules = [
                    'intGameId'=>'required',
                    'intTeamId'=>'required',
                    'intPlayerId'=>'required',
                    'intEventId'=>'required',
                    'intTourId'=>'required',
                    'PTS'=>'required',
                    'F' =>'required',
                    '3P'=>'required',
                    'FT'=>'required',
                    'FC'=>'required',
                    'AS1'=>'required',
                    'A'=>'required',
                    'B'=>'required',
                    'DR'=>'required',
                    'OR1'=>'required',
                    'S'=>'required',
                    'TO1'=>'required',
                    'E'=>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $r1 = DB::select(DB::raw(
                    'select intId from tbl_event_tournament_game_player_score where intGameId ='.$request->input('intGameId').' and intTeamId= '.$request->input('intTeamId').' and 
                    intPlayerId = '.$request->input('intEventId').' '
                    ));
 
                if(count($r1)>0){
                    // updatre case
                        foreach($r1 as $val){
                        $res = DB::select(DB::raw(' update tbl_event_tournament_game_player_score set PTS = PTS+'.$request->input('PTS').',F=F+'.$request->input('F').',
                            3P=3P+'.$request->input('3P').',FT=FT+'.$request->input('FT').',FC=FC+'.$request->input('FC').',AS1=AS1+'.$request->input('AS1').',
                            A=A+'.$request->input('A').',B=B+'.$request->input('B').',DR=DR+'.$request->input('DR').',OR1=OR1+'.$request->input('OR1').',
                            S=S+'.$request->input('S').',TO1=TO1+'.$request->input('TO1').',E=E+'.$request->input('E').' where intGameId ='.$request->input('intGameId').'
                             and intTeamId= '.$request->input('intTeamId').' and intId = '.$val->intId.' and intPlayerId = '.$request->input('intPlayerId').' '));
                    }
                }else{
                    // insert case
                    $res = DB::select(DB::raw('insert into tbl_event_tournament_game_player_score(intGameId,intTeamId,intPlayerId,PTS,F,3P,FT,FC,AS1,A,B,DR,OR1,S,TO1,E)values
                        ('.$request->input('intGameId').','.$request->input('intTeamId').','.$request->input('intPlayerId').','.$request->input('PTS').','.$request->input('F').','.$request->input('3P').','.$request->input('FT').','.$request->input('FC').','.$request->input('AS1').','.$request->input('A').','.$request->input('B').','.$request->input('B').','.$request->input('DR').','.$request->input('OR1').','.$request->input('TO1').','.$request->input('E').')'));
                }
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Game Player Score Generated',
                    'intEventId'=>$request->input('intEventId'),
                    'intTourId'=>$request->input('intTourId'),
                     'intGameId'=>$request->input('intGameId'),
                    'intTeamId'=>$request->input('intTeamId'),
                    'intPlayerId'=>$request->input('intPlayerId')
                    ),200);
       
    }


public function set_event_tour_game_winner(Request $request){
      $rules = [
                    'intGameId'=>'required',
                    'intTeamId'=>'required',
                    'intEventId'=>'required',
                    'intTourId'=>'required',
                    'in_round'=>'required',
                    'in_group'=>'required'
                ];

                $v1 = Validator::make($request->all(),$rules);
                if($v1->fails()){
                    return Response::json(array(
                            'error'=>true,
                            'content'=>$v1->errors()
                        ),200);
                }

                $r1 = DB::select(DB::raw(
                    'select a.intId,b.intId from tbl_event_tournament_game_winner a,tbl_event_tournament_game b,tbl_event_tournament c,tbl_event d where a.intGameId ='.$request->input('intGameId').' and a.winner_team_id= '.$request->input('intTeamId').' and 
                    a.intEventId = '.$request->input('intEventId').' and a.intTourId ='.$request->input('intTourId').' and a.in_round='.$request->input('in_round').' and a.in_group = '.$request->input('in_group').'
                    and a.intGameId = b.intId and a.intTourId = c.intId and a.intEventId = d.intId and a.in_round = b.in_round and a.in_group = b.in_group'
                    ));
 
                if(count($r1)>0){
                    // updatre case
                    return Response::json(array(
                        'error'=>true,
                        'content'=>'Winner Already set for this Game`s Round '.$request->input('in_round').' and group '.$request->input('in_group').' ',
                        ),200);
                }else{
                    // insert case
                    $res = DB::select(DB::raw('insert into tbl_event_tournament_game_winner(intGameId,intTourId,intEventId,in_round,in_group,winner_team_id)
                        values('.$request->input('intGameId').','.$request->input('intTourId').','.$request->input('intEventId').','.$request->input('in_round').','.$request->input('in_group').','.$request->input('intTeamId').')'));
                }
                return Response::json(array(
                    'error'=>false,
                    'content'=>'Event Tournament Game Winner for Round '.$request->input('in_round').' and group '.$request->input('in_group').' Now Generated',
                    'intEventId'=>$request->input('intEventId'),
                    'intTourId'=>$request->input('intTourId'),
                     'intGameId'=>$request->input('intGameId'),
                    'winner_team_id'=>$request->input('intTeamId')
                    ),200);
}
// fetch game score stats


public function fetch_tour_game_score_stats(Request $request){
    $rules = [
    'email'=>'required',
    'password'=>'required',
    'intGameId'=>'required',
    'intTeamId'=>'required',
    'intEventId'=>'required',
    'intTourId'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }
    $res = DB::select(DB::raw('select a.* from tbl_event_tournament_game_score_stats a,tbl_event_tournament_game b,tbl_event_tournament c,tbl_event d,tbl_event_tournament_team e 
        where a.intGameId = b.intId and a.intTourId = c.intId and a.intEventId = d.intId and a.intTeamId = e.intId 
        and a.intGameId ='.$request->input('intGameId').' and a.intEventId = '.$request->input('intEventId').' and a.intTourId = '.$request->input('intTourId').' and a.intTeamId = '.$request->input('intTeamId').'  '));


    return Response::json(array(
        'error'=>false,
        'content'=>$res
        ),200);
}

//fetch team score

public function fetch_tour_game_team_score_stats(Request $request){
    $rules = [
    'email'=>'required',
    'password'=>'required',
    'intGameId'=>'required',
    'intTeamId'=>'required',
    'intEventId'=>'required',
    'intTourId'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }
    $sql = 'select a.* from tbl_event_tournament_game_team_score a,tbl_event_tournament_game b,tbl_event_tournament c,tbl_event d,tbl_event_tournament_team e 
        where a.intGameId = b.intId and b.intTourId = c.intId and b.intEventId = d.intId and a.intTeamId = e.intId 
        and a.intGameId ='.$request->input('intGameId').' and b.intEventId = '.$request->input('intEventId').' and b.intTourId = '.$request->input('intTourId').' ';
    $res = DB::select(DB::raw('select a.* from tbl_event_tournament_game_team_score a,tbl_event_tournament_game b,tbl_event_tournament c,tbl_event d,tbl_event_tournament_team e 
        where a.intGameId = b.intId and b.intTourId = c.intId and b.intEventId = d.intId and a.intTeamId = e.intId 
        and a.intGameId ='.$request->input('intGameId').' and b.intEventId = '.$request->input('intEventId').' and b.intTourId = '.$request->input('intTourId').'  '));


    return Response::json(array(
        'error'=>false,
        'content'=>$res,
        'query'=>$sql
        ),200);
}

// fetch player score

public function fetch_tour_game_player_score_stats(Request $request){
    $rules = [
    'email'=>'required',
    'password'=>'required',
    'intGameId'=>'required',
    'intTeamId'=>'required',
    'intEventId'=>'required',
    'intTourId'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }
   
    $res = DB::select(DB::raw('select a.* from tbl_event_tournament_game_player_score a,tbl_event_tournament_game b,tbl_event_tournament c,tbl_event d,
        tbl_event_tournament_team e,tbl_event_tournament_team_players f 
        where a.intGameId = b.intId and b.intTourId = c.intId and b.intEventId = d.intId and a.intTeamId = e.intId and a.intPlayerId = f.intId  
        and a.intGameId ='.$request->input('intGameId').' and b.intEventId = '.$request->input('intEventId').' and b.intTourId = '.$request->input('intTourId').'
         and a.intTeamId = '.$request->input('intTeamId').'  '));

    return Response::json(array(
        'error'=>false,
        'content'=>$res,
        ),200);
}



// fetch all games 

    public function fetch_event_tour_all_games(Request $request){
         $rules = [
    'email'=>'required',
    'password'=>'required',
    'intEventId'=>'required',
    'intTourId'=>'required',
    'in_round'=>'required',
    'in_group'=>'required'
    ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }
   $sql  ='select distinct a.* from tbl_event_tournament_game a,tbl_event_tournament b,tbl_event c,tbl_event_tournament_team d
        where  a.intTourId = b.intId and a.intEventId = c.intId and a.intFirstTeamId = d.intId and a.intSecondTeamId = d.intId  
        and  a.intEventId = '.$request->input('intEventId').' and a.intTourId = '.$request->input('intTourId').'  ';

    $res = DB::select(DB::raw('select distinct a.* from tbl_event_tournament_game a,tbl_event_tournament b,tbl_event c,tbl_event_tournament_team d
        where  a.intTourId = b.intId and a.intEventId = c.intId and (a.intFirstTeamId = d.intId or a.intSecondTeamId = d.intId  )
        and  a.intEventId = '.$request->input('intEventId').' and a.intTourId = '.$request->input('intTourId').'  '));

    return Response::json(array(
        'error'=>false,
        'content'=>$res,
        'query'=>$sql
        ),200);
    }
    


// fetch all teams regarding to tournament

 public function fetch_event_tour_all_teams(Request $request){
         $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intGameId'=>'required'
        ];

    $v1 = Validator::make($request->all(),$rules);
    if($v1->fails()){
        return Response::json(array(
            'error'=>true,
            'content'=>$v1->errors()
            ),200);
    }
 

    $res = DB::select(DB::raw('select distinct a.* from tbl_event_tournament_team a,tbl_event_tournament b,tbl_event c,tbl_event_tournament_game d
        where  a.intTourId = b.intId and b.intEventId = c.intId and (d.intFirstTeamId = a.intId or d.intSecondTeamId = a.intId  )
        and  b.intEventId = '.$request->input('intEventId').' and a.intTourId = '.$request->input('intTourId').' and d.intId = '.$request->input('intGameId').'  '));

    return Response::json(array(
        'error'=>false,
        'content'=>$res
        ),200);
    }


// fetch all team players regarding to tournament

    public function fetch_event_tour_all_players_per_team(Request $request){
         $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intGameId'=>'required',
            'intTeamId'=>'required'
        ];

        $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v1->errors()
                ),200);
        }

        $res = DB::select(DB::raw(
            'select distinct a.* from tbl_event_tournament_team_players a,tbl_event_tournament b,tbl_event c,tbl_event_tournament_game d,tbl_event_tournament_team e
            where  a.intTourId = b.intId and b.intEventId = c.intId and (d.intFirstTeamId = a.intId or d.intSecondTeamId = a.intId  ) and a.intTeamId = e.intId
            and  b.intEventId = '.$request->input('intEventId').' and a.intTourId = '.$request->input('intTourId').' and d.intId = '.$request->input('intGameId').' 
            and a.intTeamId = '.$request->input('intTeamId').' '
            ));

        return Response::json(array(
            'error'=>false,
            'content'=>$res
            ),200);
    }

    public function update_tour_player_accept_status(Request $request){
       $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intGameId'=>'required',
            'intTeamId'=>'required',
            'intPlayerId'=>'required',
            'status'=>'required'
        ];

        $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v1->errors()
                ),200);
        }

        $res = DB::select(DB::raw(
            'update tbl_event_tournament_team_players set accept_status = "'.$request->input('status').'" where intTourId = '.$request->input('intTourId').' 
            and intTeamId='.$request->input('intTeamId').' and intId = '.$request->input('intPlayerId').' '
            ));

        return Response::json(array(
            'error'=>false,
            'content'=>'Player Status Updated'
            ),200);
    }

    public function fetch_tour_player_status(Request $request){
        $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intGameId'=>'required',
            'intTeamId'=>'required',
            'intPlayerId'=>'required'
        ];

        $v1 = Validator::make($request->all(),$rules);
        if($v1->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v1->errors()
                ),200);
        }

        $res = DB::select(DB::raw(
            'select a.* from tbl_event_tournament_team_players a,tbl_event_tournament b,tbl_event c,tbl_event_tournament_team d,tbl_event_tournament_game e where 
            a.intTourId = b.intId and a.intTeamId = d.intId and b.intEventId = c.intId and b.intId = e.intTourId and a.intTourId = '.$request->input('intTourId').' and
            a.intTeamId = '.$request->input('intTeamId').' and b.intId = d.intTourId and d.intId = '.$request->input('intGameId').'  '
            ));

        return Response::json(array(
            'error'=>false,
            'content'=>$res
            ),200);
    }


    //delete Event

    public function delete_event(Request $request){
        $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
            'delete from tbl_event where intId ='.$request->input('intEventId').' and intUserId='.$request->intput('intUserId').' '
            ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Deleted'
            ),200);
    } 

    public function delete_tour_team(Request $request){
        $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intTeamId'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
           'delete a from tbl_event_tournament_team a
           LEFT JOIN tbl_event_tournament b on a.intTourId = b.intId 
           where a.intId = '.$request->input('intTeamId').' and a.intTourId = '.$request->input('intTourId').' and 
           b.intEventId = '.$request->input('intEventId').'  '
            ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Deleted'
            ),200);
    }


    public function delete_tour_team_player(Request $request){
            $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intTeamId'=>'required',
            'intPlayerId'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
           'delete a from tbl_event_tournament_team_players a
           LEFT JOIN tbl_event_tournament_team b on a.intTeamId = b.intId 
           LEFT JOIN tbl_event_tournament c on a.intTourId = c.intId 
           where a.intTeamId = '.$request->input('intTeamId').' and a.intTourId = '.$request->input('intTourId').' and 
           b.intEventId = '.$request->input('intEventId').' and a.intId ='.$request->input('intPlayerId').'  '
            ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Deleted'
            ),200);    
    }

    public function delete_tour_all_team(Request $request){
        $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
           'delete a from tbl_event_tournament_team a
           LEFT JOIN tbl_event_tournament b on a.intTourId = b.intId 
           where  a.intTourId = '.$request->input('intTourId').' and 
           b.intEventId = '.$request->input('intEventId').'  '
            ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Deleted'
            ),200);
    }

    public function delete_tour_team_all_players(Request $request){
         $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intTourId'=>'required',
            'intTeamId'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
           'delete a from tbl_event_tournament_team_players a
           LEFT JOIN tbl_event_tournament_team b on a.intTeamId = b.intId 
           LEFT JOIN tbl_event_tournament c on a.intTourId = c.intId 
           where a.intTeamId = '.$request->input('intTeamId').' and a.intTourId = '.$request->input('intTourId').' and 
           b.intEventId = '.$request->input('intEventId').' '
            ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Deleted'
            ),200);    
    }



    ///////////////////////////////////////////////
    ///////////////////////////////////////////////
    ///////////////Game Practice///////////////////
    ///////////////////////////////////////////////
    ///////////////////////////////////////////////
    

    public function add_event_practice_details(Request $request){
        $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required',
            'practice_date'=>'required',
            'practice_desc'=>'required',
            'same_time_status'=>'required',
            'practice_time'=>'required',
            'repeat_status'=>'required',
            'repeat_count'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
           'insert into tbl_event_practice_details(intEventId,intUserId,practice_date,practice_desc,same_time_status,practice_time,repeat_status,repeat_count)
           values('.$request->input('intEventId').','.$request->input('intUserId').',"'.$request->input('practice_date').'","'.$request->input('practice_desc').'",
            "'.$request->input('same_time_status').'","'.$request->input('practice_time').'","'.$request->input('repeat_status').'","'.$request->input('repeat_count').'")'
            ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Details Generated'
            ),200);    
    }

public function delete_event_practice(Request $request){
    $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required',
            'intPracId'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
            'delete from tbl_event_practice_details where intId = '.$request->input('intPracId').' '
             ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Details Deleted'
            ),200);    
}

public function update_event_practice_details(Request $request){
     $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required',
            'intPracId'=>'required',
            'practice_date'=>'required',
            'practice_desc'=>'required',
            'same_time_status'=>'required',
            'practice_time'=>'required',
            'repeat_status'=>'required',
            'repeat_count'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw(
          'update tbl_event_practice_details set practice_date ="'.$request->input('practice_date').'",practice_desc = "'.$request->input('practice_desc').'",
          same_time_status = "'.$request->input('same_time_status').'",practice_time = "'.$request->input('practice_time').'",repeat_status = "'.$request->input('repeat_status').'",
          repeat_count = "'.$request->input('repeat_count').'" where intId = '.$request->input('intPracId').' '
                      ));
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Details Updated',
            'intPracId'=>$request->input('intPracId'),
            'intEventId'=>$request->input('intEventId'),
            ),200);  
}


public function add_event_practice_location(Request $request){
    $rules = [
            'email'=>'required',
            'password'=>'required',
            'intPracId'=>'required',
            'location_name'=>'required',
            'location_time'=>'required',
            'location_date'=>'required'
                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::table('tbl_event_practice_location')->insertGetId([
            'intPracId'=>$request->input('intPracId'),
            'location_name'=>$request->input('location_name'),
            'location_date'=>$request->input('location_date'),
            'location_time'=>$request->input('location_time')
            ]);
        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Location Created',
            'intPracId'=>$request->input('intPracId'),
            'recent_id'=>$res,
            ),200);  
}


public function delete_event_practice_location(Request $request){
    
    $rules = [
            'email'=>'required',
            'password'=>'required',
            'intPracId'=>'required',
            'intId'=>'required'
                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw('delete from tbl_event_practice_location where intId ='.$request->input('intId').' and intPracId = '.$request->input('intPracId').' '));

        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Location Deleteted',
            'intPracId'=>$request->input('intPracId'),
            'recent_id'=>$request->input('intId'),
            ),200);  
}


public function add_event_practice_players(Request $request){
   
   $rules = [
            'email'=>'required',
            'password'=>'required',
            'intCoachId'=>'required',
            'player_name'=>'required',
            'player_profile'=>'required',
            'accept_status'=>'required',
            'player_adv_image'=>'required',
            'intPracId'=>'required',
            'intEventId'=>'required'
                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }

         $destinationPath =   base_path() . '/public/uploads/';
                     //$file = $request->file('file');
                    
                     if (!($request->hasFile('player_profile')) && !($request->hasFile('player_adv_image'))) {
                        return Response::json(array(
                              'error' => true,
                              'response' =>'File Not Exist'
                              ),
                              200
                              );
                       //
                     

                 }else{
                      $profile_image =rand(11111,99999).Auth::user()->intId.'.'.$request->file('player_profile')->getClientOriginalExtension();

                     $fll =  $request->file('player_profile')->move($destinationPath, $profile_image);


                     // file two upload

                      $player_adv_image =rand(11111,99999).Auth::user()->intId.'.'.$request->file('player_adv_image')->getClientOriginalExtension();

                     $fll =  $request->file('player_adv_image')->move($destinationPath, $player_adv_image);
                 }



        $res = DB::table('tbl_event_practice_players')->insertGetId([
                'intPracId'=>$request->input('intPracId'),
                'intCoachId'=>$request->input('intCoachId'),
                'player_name'=>$request->input('player_name'),
                'player_profile'=>$profile_image,
                'accept_status'=>$request->input('accept_status'),
                'player_adv_image'=>$player_adv_image
            ]);

        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Player Added',
            'intPracId'=>$request->input('intPracId'),
            'recent_id'=>$res,
            'intEventId'=>$request->input('intEventId'),
            'intCoachId'=>$request->input('intCoachId')
            ),200);  
}


public function delete_event_practice_player(Request $request){
      $rules = [
            'email'=>'required',
            'password'=>'required',
            'intPracId'=>'required',
            'intId'=>'required',
            'intEventId'=>'required',

                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw('delete a from tbl_event_practice_players a
            LEFT JOIN tbl_event_practice_details b on a.intPracId  = b.intId
            LEFT JOIN tbl_event c on b.intEventId = c.intId 
         where a.intId ='.$request->input('intId').' and a.intPracId = '.$request->input('intPracId').' and b.intEventId = '.$request->input('intEventId').' '));

        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Player Deleteted',
            'intPracId'=>$request->input('intPracId'),
            'recent_id'=>$request->input('intId'),
            ),200);  
}


public function delete_event_practice_all_player(Request $request){
     $rules = [
            'email'=>'required',
            'password'=>'required',
            'intPracId'=>'required',
            'intEventId'=>'required',

                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw('delete a from tbl_event_practice_players a
            LEFT JOIN tbl_event_practice_details b on a.intPracId  = b.intId
            LEFT JOIN tbl_event c on b.intEventId = c.intId 
         where a.intPracId = '.$request->input('intPracId').' and b.intEventId = '.$request->input('intEventId').'  '));

        return Response::json(array(
            'error'=>false,
            'content'=>'Event Practice Player Deleteted',
            'intPracId'=>$request->input('intPracId'),
            'recent_id'=>$request->input('intId'),
            ),200);  
}

public function fetch_event_all_practice_players(Request $request){
     $rules = [
            'email'=>'required',
            'password'=>'required',
            'intPracId'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required'

                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        // $deliveries = DB::select('
        //     select a.* from tbl_event_practice_players a,tbl_event_practice_details b,tbl_event c,tbl_user_register d where 
        //     a.intPracId = b.intId and a.intCoachId = d.intId and b.intEventId = c.intId and a.intPracId = '.$request->input('intPracId').' and
        //     a.intCoachId = '.$request->input('intUserId').' and b.intEventId = '.$request->input('intEventId').'
        //      ');

    //     //$res= DB::table('tbl_event_practice_players')->paginate(1);
    //     // $res =  DB::table("tbl_event_practice_players")
    //     //     ->selectRaw("*")
    //     //     ->leftJoin('tbl_event_practice_details','tbl_event_practice_players.intPracId', '=', 'tbl_event_practice_details.intId')
    //     //     ->orderBy('tbl_event_practice_players.intId','DESC')
    //     //     ->take(1)
    //     //     ->offset((1) * 1)
    //     //     ->get();
    //     //     $data = new Paginator($res, count($res), 1, 1, array("path" => '/tags'));

    //       $deliveries = collect($deliveries);
    // $perPage = 0;
    // $currentPage =($request->input('page')) ? $request->input('page') : 1;
    // $slice_init = ($currentPage == 1) ? 0 : (($currentPage*$perPage)-$perPage);
    // $pagedData = $deliveries->slice($slice_init, $perPage)->all();
    // $deliveries = new LengthAwarePaginator($pagedData, count($deliveries), $perPage, $currentPage);
    // $deliveries ->setPath('http://localhost/basket/new1/event/fetch_event_all_players/');
    // return $deliveries;


         $object = DB::table('tbl_event_practice_players as a')
            ->selectRaw('a.*')
            ->leftJoin('tbl_event_practice_details as b','a.intPracId', '=', 'b.intId')
            ->leftJoin('tbl_event as c','b.intEventId', '=', 'c.intId')
            ->leftJoin('tbl_user_register as d','a.intCoachId', '=', 'd.intId')
            ->orderBy('a.intId', 'ASC')
            ->where('a.intCoachId', $request->input('intUserId'))
            ->where('b.intEventId', $request->input('intEventId'))
            ->where('a.intPracId', $request->input('intPracId'))
            ->paginate(10);

        return Response::json(array(
            'error'=>false,
            'content'=>$object->toArray(),
            ),200);  
}   

public function fetch_event_all_practice(Request $request){
     $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required'

                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        // $res = DB::select(DB::raw('
        //     select a.* from tbl_event_practice_details a,tbl_event b,tbl_user_register c where a.intEventId = b.intId and a.intUserId = c.intId and
        //     a.intEventId = '.$request->input('intEventId').' and a.intUserId = '.$request->input('intUserId').' order by a.intId 
        //                  '));


        $res = DB::table('tbl_event_practice_details as a')
                    ->select('a.*')
                    ->join('tbl_event as b','a.intEventId', '=', 'b.intId')
                    ->join('tbl_user_register as c','a.intUserId', '=', 'c.intId')
                    ->orderBy('a.intId', 'ASC')
                    ->where('a.intEventId', $request->input('intEventId'))
                    ->where('a.intUserId', $request->input('intUserId'))
                    ->paginate(10);

        return Response::json(array(
            'error'=>false,
            'content'=>$res->toArray(),
            ),200);  
}

public function fetch_event_single_practice(Request $request){
     $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required',
            'intId' =>'required'

                    ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }
        $res = DB::select(DB::raw('
            select a.* from tbl_event_practice_details a,tbl_event b,tbl_user_register c where a.intEventId = b.intId and a.intUserId = c.intId and
            a.intEventId = '.$request->input('intEventId').' and a.intUserId = '.$request->input('intUserId').' and a.intId = '.$request->input('intId').'
            order by a.intId desc
                         '));

        return Response::json(array(
            'error'=>false,
            'content'=>$res,
            ),200);  
}





////////////////////////////////////////////////////
////////////////////////////////////////////////////
//////////////Pickup Game Module////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////

public function add_event_pickup_details(Request $request){
     $rules = [
            'email'=>'required',
            'password'=>'required',
            'intEventId'=>'required',
            'intUserId'=>'required',
            'pickup_game_date'=>'required',
            'pickup_game_name'=>'required',
            'same_time_status'=>'required',
            'pickup_game_time'=>'required',
            'same_location_status'=>'required',
            'repeat_count'=>'required',
            'repeat_game'=>'required',
            'conversation_status'=>'required',
            'auto_cancel'=>'required',
            'cancel_type'=>'required',
            'cancel_high_temp'=>'required',
            'cancel_low_temp'=>'required'
        ];

        $v = Validator::make($request->all(),$rules);
        if($v->fails()){
            return Response::json(array(
                'error'=>true,
                'content'=>$v->errors()
                ),200);
        }

        $res = DB::table('tbl_event_pickup_game_details')->insertGetId([
            'intEventId'=>$request->input('intEventId'),
            'intUserId'=>$request->input('intUserId'),
            'pickup_game_name'=>$request->input('pickup_game_name'),
            'pickup_game_date'=>$request->input('pickup_game_date'),
            'same_time_status'=>$request->input('same_time_status'),
            'same_location_status'=>$request->input('same_location_status'),
            'repeat_count'=>$request->input('repeat_count'),
            'repeat_game'=>$request->input('repeat_count')
            ]);

        $last_insert_id = $res;

        $res1 = DB::table('tbl_event_pickup_game_settings')->insertGetId([
                'intPgameId'=>$last_insert_id,
                'conversation_status'=>$request->input('conversation_status'),
                'auto_cancel'=>$request->input('auto_cancel'),
                'cancel_type'=>$request->input('cancel_type'),
                'cancel_high_temp'=>$request->input('cancel_high_temp'),
                'cancel_low_temp'=>$request->input('cancel_low_temp')
            ]);

        return Response::json(array(
            'error'=>false,
            'content'=>'Event PIckup Game Details Generated'
            ),200);    
}



public function delete_event_pickup_details(Request $request){
    
}
















    

















}






// add team players