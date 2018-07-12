<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;
use App\Models\Team;

class BaseController extends Controller {
  protected $modelName = '';
  protected $model = null;
  protected $rules = [];
  protected $user = null;
  protected $teamId = '';

  public function __construct() {
    if(!empty($this->modelName)) {
      $className = $this->modelName == 'User' ? "App\\User" : "App\\Models\\".$this->modelName;
      $this->model = new $className;
    }
  }

  protected function getInput($input=null, $rules=null)
  {
    if(is_null($input)) {
      $input = \Input::all();
    }
    if(is_null($rules)) {
      $rules = $this->rules;
    }
    return array_intersect_key($input, array_flip(array_keys($rules)));
  }

  public function show($id) {
    $record = $this->model->find($id);
    $record = $this->onShowRecordReady($record);
    return response()->json([
      'status'=>true,
      'result'=>$record
    ]);
  }

  public function destroy($record) {
    $record->delete();
  }
}