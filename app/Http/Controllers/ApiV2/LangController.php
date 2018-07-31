<?php namespace App\Http\Controllers\ApiV2;

class Langcontroller extends BaseAuthController {
  protected $modelName = 'Lang';

  public function index() {
    return response()->json([
      'status'=>true,
      'result'=>$this->model->whereEnabled(1)->get()
    ]);
  }
}