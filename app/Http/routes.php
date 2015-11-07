<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/', 'DoorChatController@index');
Route::controllers([
    'auth' => 'Auth\AuthController', 
    'password' => 'Auth\PasswordController',
]);




Route::group(array('prefix'=>'v1'),function(){

	Route::post('user_register','Auth\AuthController@postRegister');
	Route::post('user_login','Auth\AuthController@postLogin');
	Route::get('user_register','Auth\AuthController@getRegister');
	Route::get('user_login','Auth\AuthController@getLogin');

	Route::post('user_register_fb','Auth\AuthController@postRegisterFb');
	Route::get('user_register_fb','Auth\AuthController@getRegisterFb');

	Route::get('getEmail','Auth\PasswordController@getEmail');
	Route::post('postEmail','Auth\PasswordController@postEmail');
	Route::get('password/reset/{token}','Auth\PasswordController@getReset');
	Route::get('/','DoorChatController@index');
	Route::post('edit_user_profile','DoorChatController@edit_user_profile');
	Route::post('get_user_profile','DoorChatController@get_user_profile');

	Route::post('create_door','DoorChatController@create_door');
	Route::post('update_door_info','DoorChatController@update_door_info');
	Route::post('delete_door','DoorChatController@delete_door');
	Route::post('delete_visited_door','DoorChatController@delete_visited_door');
	Route::post('report_door','DoorChatController@report_door');
	Route::post('add_visited_door','DoorChatController@add_visited_door');
	Route::post('add_report_door','DoorChatController@add_report_door');

	// Door Post Module
	Route::post('add_door_post','DoorChatController@add_door_post');
	Route::post('delete_door_post','DoorChatController@delete_door_post');
	Route::post('update_door_post','DoorChatController@update_door_post');
	Route::post('door_post_like','DoorChatController@door_post_like');
//	Route::post('fetch_door_post','DoorChatController@fetch_door_post');
	// Route::get('fetch_door_post/{page}/', array(
	//     'as'    => 'fetch_door_post',
	//     'uses'  => 'DoorChatController@fetch_door_post'
	// ));
	Route::post('fetch_door_post/{page}','DoorChatController@fetch_door_post');

	Route::post('add_door_post_comment','DoorChatController@add_door_post_comment');
	Route::post('delete_door_post_comment','DoorChatController@delete_door_post_comment');
	Route::post('update_door_post_comment','DoorChatController@update_door_post_comment');
	Route::post('door_post_comment_like','DoorChatController@door_post_comment_like');
	Route::post('fetch_door_post_comment','DoorChatController@fetch_door_post_comment');


	Route::post('my_door_screen','DoorChatController@my_door_screen');
	Route::post('manage_door_list','DoorChatController@manage_door_list');

	// fetch all my doors 
	Route::post('fetch_mydoor_list','DoorChatController@fetch_mydoor_list');
	// fetch all neightbour door
	Route::post('fetch_neighbour_list','DoorChatController@fetch_neighbour_list');
	// fetch single my door and neighbour door detailed view and apply order most visited/ most voted/most viewed
	Route::post('fetch_door_single','DoorChatController@fetch_door_single');

	// connect to door
	Route::post('connect_to_door','DoorChatController@connect_to_door');

	// share post timeline
	Route::post('share_post_timeline','DoorChatController@share_post_timeline');

	// delete unused door which has no activity since 30 days
	Route::post('delete_unused_door','DoorChatController@delete_unused_door');
	
	// // fetch single neighbour door detailed view and apply order most visited/ most voted/most viewed 
	// Route::post('fetch_neighbour_single','DoorChatControlsler@fetch_neighbour_single');
	
});
	


	
