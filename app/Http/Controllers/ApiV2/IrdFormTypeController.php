<?php namespace App\Http\Controllers\ApiV2;

class IrdFormTypeController extends BaseAuthController {
  protected $modelName = 'IrdFormType';

  public function index() {
    $rows = $this->model->orderBy('seq_no')->get();
    foreach( $rows as $row ) {
      $row->forms;
    }
    return response()->json([
      'status'=>true,
      'result'=>$rows
    ]);
  }
}