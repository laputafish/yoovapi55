<?php namespace App\Http\Controllers\ApiV2;

class EmployeeTerminationController extends BaseController {
  public function index() {
    return response()->json([
      'status'=>true,
      'result'=>[
        'data'=>[],
        'total'=>0
      ]
    ]);
  }
}