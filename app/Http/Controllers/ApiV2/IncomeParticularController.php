<?php namespace App\Http\Controllers\ApiV2;

use App\Models\IncomeParticular;

class IncomeParticularController extends BaseController {
  protected $modelName = 'IncomeParticular';

  public function index() {
    $teamId = \Input::get('teamId', '');
    $rows = IncomeParticular::all();
    foreach( $rows as $row ) {
      $teamIncomeParticular = $row->teamIncomeParticulars()->whereTeamId($teamId)->first();
      if (isset($teamIncomeParticular)) {
        $row->pay_type_ids = $row->teamIncomeParticulars->payTypes()->lists('pay_type_id')->toArray();
      } else {
        $row->pay_type_ids = [];
      }
    }
    return response()->json([
      'status'=>true,
      'result'=>$rows
    ]);
  }
}