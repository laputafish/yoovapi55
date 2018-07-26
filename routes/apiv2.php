<?php
use Illuminate\Http\Request;
use App\Models\Media;
use App\Models\Folder;
use App\Helpers\MpfHelper;

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

Route::get('/test/{formId}/{employeeId}', function($formId, $employeeId) {
  $form = \App\Models\FormCommencement::find($formId);
  $formEmployee = $form->employees()->whereEmployeeId($employeeId)->first();
  $ir56e = \App\Helpers\IrData\IR56eHelper::get($form, $formEmployee);
  dd($ir56e);
});

Route::get('/users/attachFolder', function() {
  $rootFolder = Folder::whereName('root')->first();
  $usersFolder = Folder::whereName('users')->first();
  if(is_null($usersFolder)) {
    $usersFolder = Folder::create([
      "name"=>"users",
      "description"=>"Users"
    ]);
  }
  $usersFolder->parent()->associate($rootFolder)->save();

  $detachedFolders = Folder::where('name', '!=', 'root')->whereNull('parent_id')->get();
  foreach( $detachedFolders as $detachedFolder ) {
    $detachedFolder->parent()->associate($usersFolder)->save();
  }
  dd($detachedFolders->toArray());
});

Route::get('/version', function() {
    return 'API Version 2';
});

Route::get('/mpf', function() {
  return MpfHelper::generate();
});

//*******************
// Login OA
//*******************
Route::post('/auth/login_oa',
  'Auth\OAAuthController@login');

//  function() {
//    $url = 'https://hr.yoov.com/api/v1/t/auth/login';
//    $data = [
//      'email' => 'dominic@yoov.com',
//      'password' => 'lmf26891',
//      'teamId' => null
//    ];
//    $options = array(
//      'http' => array(
//        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//        'method'  => 'POST',
//        'content' => http_build_query($data)
//      )
//    );
//    $context  = stream_context_create($options);
//    $result = file_get_contents($url, false, $context);
//
//    if ($result === FALSE) {
//      return response()->json([
//        'status' => false,
//        'message' => 'Access Denied'
//      ]);
//    } else {
//      $data = json_decode($result);
//      return response()->json($data);
//    }
////    $data = json_decode( $result, true );
////    print_r( $data );
//  }
//);

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

Route::get('test/{count}', 'TestController@insertRecords');
Route::get('media/icons/{id}', 'MediaController@getIcon');
Route::get('media/icons/defaults/{name}', 'MediaController@getDefaultIcon');
Route::get('media/image/{id}', 'MediaController@getImage');
Route::get('media/static_forms/{name}', 'StaticFormController@getFormImage');
Route::get('media/document/{id}', 'MediaController@showDocument');
Route::get('media/tax_forms/{id}', 'MediaController@showTaxForm');

Route::get('media/forms/commencements/{formId}/{employeeId}', 'MediaController@showCommencementForm');
Route::get('media/forms/salaries/{formId}/{employeeId}', 'MediaController@showSalaryForm');
Route::get('media/forms/terminations/{formId}/{employeeId}', 'MediaController@showTerminationForm');
Route::get('media/forms/departures/{formId}/{employeeId}', 'MediaController@showDepartureForm');
Route::get('media/ird_forms/{formId}/{employeeId}', 'MediaController@showIrdForm');

Route::get('media/download/{id}', 'MediaController@downloadDocument');
Route::get('media/download_documents/{ids}', 'MediaController@downloadDocumentsInZip');

Route::get('ird_forms/{employeeFormId}/icon', 'IrdFormController@showFormIcon');
Route::get('ird_forms/{employeeFormId}', 'IrdFormController@showFormPdf');

//Route::get('xmedia/download/{id}', function($id) {
//  $media = Media::find($id);
//  $filename = urlencode($media->filename);
//  $url = 'apiv2/media/download/'.$id.'/'.$filename;
//  return redirect($url);
//});


// temporary for debugging
Route::get('employees/{employeeId}/forms', 'TestFormController@generateForm');

// Copy form template variables
Route::get('file_fields/copy/{fromId}/{toId}', 'TestFormController@copyTemplateFields');

Route::get('users/init', 'UserController@init');
Route::group(['middleware'=>'auth:api'], function() {
    Route::get('user', 'UserController@getUser');
    Route::resource('users', 'UserController');
    Route::get('products/init', 'ProductController@init');
    Route::resource('products', 'ProductController');
    Route::resource('meeting_rooms', 'MeetingRoomController');
    Route::resource('meeting_room_bookings', 'MeetingRoomBookingController');
    Route::resource('meetings', 'MeetingController');
    Route::resource('equipments', 'EquipmentController');
    Route::get('/folders/init', 'FolderController@init');
    Route::resource('folders', 'FolderController');
    Route::resource('documents', 'DocumentController');
    Route::resource('tax_forms', 'TaxFormController');
    Route::post('media/upload', 'MediaController@uploadDocument');
    Route::resource('income_particulars', 'IncomeParticularController', ['only'=>['index','store']]);
    Route::resource('tax_form_settings', 'TaxFormSettingController', ['only'=>['index','store']]);

    Route::resource('employee_commencements', 'EmployeeCommencementController');
    Route::resource('employee_terminations', 'EmployeeTerminationController');
    Route::resource('employee_salaries', 'EmployeeSalaryController');
    Route::resource('employee_departures', 'EmployeeDepartureController');

    Route::resource('ird_form_types', 'IrdFormTypeController');
    Route::resource('ird_forms', 'IrdFormController');
    Route::resource('forms', 'FormController');

    Route::resource('sample_forms', 'SampleFormController');
    Route::resource('oa_token', 'OATokenController', ['only'=>['store']]);
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
