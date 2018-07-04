<?php namespace App\Http\Controllers\ApiV2;

use App\Models\IncomeParticular;
use App\Models\TeamIncomeParticular;
use App\Models\TeamIncomeParticularPayType;

class IncomeParticularController extends BaseController {
  protected $modelName = 'IncomeParticular';

  public function index() {
    $teamId = \Input::get('teamId', '');
    $rows = IncomeParticular::orderby('seq_no')->get();
    foreach( $rows as $row ) {
      $teamIncomeParticular = $row->teamIncomeParticulars()->whereTeamId($teamId)->first();
      $row->pay_type_ids = [];
      if (isset($teamIncomeParticular)) {
        $payTypes = $teamIncomeParticular->payTypes;
        if(isset($payTypes)) {
          $row->pay_type_ids = $payTypes->map(function($item) {
            return (string)$item->pay_type_id;
          })->toArray();
        }
      }
    }
    return response()->json([
      'status'=>true,
      'result'=>$rows
    ]);
  }

  public function store() {
    $teamId = \Input::get('teamId');
    $incomeParticulars = \Input::get('incomeParticulars');
    foreach($incomeParticulars as $particular) {
      $particularId = $particular['id'];
      $record = IncomeParticular::find($particularId);

      $teamIncomeParticular = TeamIncomeParticular::whereTeamId($teamId)->whereIncomeParticularId($particularId)->first();
      if(is_null($teamIncomeParticular)) {
        $teamIncomeParticular = teamIncomeParticular::create([
          'team_id' => $teamId
        ]);
      }
      $record->teamIncomeParticulars()->save($teamIncomeParticular);

      // save pay type ids
      $payTypeIds = $particular['pay_type_ids'];
      TeamIncomeParticularPayType::whereTeamIncomeParticularId( $teamIncomeParticular->id )
        ->whereNotIn('pay_type_id', $payTypeIds)->delete();

      foreach($payTypeIds as $payTypeId) {
        if(TeamIncomeParticularPayType::whereTeamIncomeParticularId( $teamIncomeParticular->id )
          ->wherePayTypeId($payTypeId)->count() == 0) {
//          echo 'teamIncomeParticularId = '.$teamIncomeParticular->id; nl();
//          echo 'payTypeId = '.$payTypeIds
          $payType = TeamIncomeParticularPayType::create([
            'pay_type_id' => $payTypeId,
            'team_income_particular_id' => $teamIncomeParticular->id
          ]);
        }
      }
    }
    return response()->json([
      'status'=>true
    ]);
  }
}