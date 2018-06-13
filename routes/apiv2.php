<?php
use Illuminate\Http\Request;
use App\Models\Media;
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
//Route::get('test', function() {
//  $now = date('Y-m-d H:i:s');
//  $yesterday = date('2018-05-30 16:19:00');
//
//  dd( $now>$yesterday ? 'yes' :'no');
//});

Route::get('media/icons/{id}', 'MediaController@getIcon');
Route::get('media/icons/defaults/{name}', 'MediaController@getDefaultIcon');
Route::get('media/image/{id}', 'MediaController@getImage');
Route::get('media/document/{id}', 'MediaController@showDocument');
Route::get('media/download/{id}', 'MediaController@downloadDocument');
Route::get('media/download_documents/{ids}', 'MediaController@downloadDocumentsInZip');
//Route::get('xmedia/download/{id}', function($id) {
//  $media = Media::find($id);
//  $filename = urlencode($media->filename);
//  $url = 'apiv2/media/download/'.$id.'/'.$filename;
//  return redirect($url);
//});

Route::get('users/init', 'UserController@init');
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
    Route::post('media/upload', 'MediaController@uploadDocument');
//    Route::get('users/init', 'UserController@init');
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
