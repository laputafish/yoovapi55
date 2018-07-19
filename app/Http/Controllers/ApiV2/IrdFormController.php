<?php namespace App\Http\Controllers\ApiV2;

class IrdFormController extends BaseAuthController {
  protected $modelName = 'IrdForm';

  public function index() {
    return response()->json([
      'status'=>true,
      'result'=>$this->model->orderBy('form_date', 'desc')->get()
    ]);
  }
}
