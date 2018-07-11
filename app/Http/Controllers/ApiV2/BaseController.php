<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;

class BaseController extends Controller {
  protected $modelName = '';
  protected $model = null;
  protected $rules = [];

  public function __construct() {
    if(!empty($this->modelName)) {
      $className = $this->modelName == 'User' ? "App\\User" : "App\\Models\\".$this->modelName;
      $this->model = new $className;
    }
  }

  public function getUser() {
    return request()->user();
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

  public function destroy($record) {
    $record->delete();
  }
}