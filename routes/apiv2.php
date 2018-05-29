<?php
use Illuminate\Http\Request;

// this apiv2.php doesn't require auth.
// for testing purpose temporarily

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/test', function(Request $request) {
//  return 'test';
//});

Route::get('/', function() {
    return redirect()->to('/apiv2/version');
});

Route::get('/version', function() {
    return 'API Version 2';
});

//Route::post('/xxx', function() {
//    return 'API Version 2 post';
//});

//Route::middleware('auth:api')->get('/user', function( Request $request) {
//    return $request->user();
//});
Route::get('media/icons/{id}', 'MediaController@getIcon');
Route::get('media/image/{id}', 'MediaController@getImage');

Route::group(['middleware'=>'auth:api'], function() {
    Route::get('user', 'UserController@getUser');
    Route::get('products/init', 'ProductController@init');
    Route::resource('products', 'ProductController');
    Route::resource('meeting_rooms', 'MeetingRoomController');
    Route::resource('meeting_room_bookings', 'MeetingRoomBookingController');
    Route::resource('meetings', 'MeetingController');
    Route::resource('equipments', 'EquipmentController');
    Route::get('/folders/init', 'FolderController@init');
    Route::resource('folders', 'FolderController');
    Route::resource('documents', 'DocumentController');
    Route::get('users/init', 'UserController@init');
});


Route::post('register', 'Auth\RegisterController@register');
Route::post('registered', function() {
  dd('post: registered');
});
Route::get('registered', function() {
  dd('get: registered');
});
// Route::post('/auth', 'LoginController@authenticate');
//Route::middleware('auth:api')->group(function () {
//    Route::get('user', 'UserController@getUser');
//    Route::get('products/init', 'ProductController@init');
//    Route::resource('products', 'ProductController');
//    Route::resource('meeting_rooms', 'MeetingRoomController');
//    Route::resource('meetings', 'MeetingController');
//    Route::get('users/init', 'UserController@init');
//});
//
