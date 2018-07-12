<?php namespace App\Http\Controllers\ApiV2;

class EmployeeTerminationController extends BaseIRDFormController {
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