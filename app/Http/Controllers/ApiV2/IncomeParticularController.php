<?php namespace App\Http\Controllers\ApiV2;

use App\Models\IncomeParticular;
use App\Models\TeamIncomeParticular;
use App\Models\xxxTeamIncomeParticularPayType;

class IncomeParticularController extends BaseAuthController {
  protected $modelName = 'IncomeParticular';

  public function index() {
    $teamId = \Input::get('teamId', '');
    $rows = IncomeParticular::orderby('seq_no')->get();
    foreach( $rows as $row ) {
      $teamIncomeParticular = $row->teamIncomeParticulars()->whereTeamId($teamId)->first();
      $row->pay_type_ids = [];
      if (isset($teamIncomeParticular)) {
        $payTypeIds = trim($teamIncomeParticular->pay_type_ids);
//        echo 'payTypeIds: '.$payTypeIds; nl();
        $row->pay_type_ids = empty($payTypeIds) ? [] : explode(',', $payTypeIds);
//        trim($pa))
//        if(isset($payTypeIds)) {
//          $row->pay_type_ids = explode(',',$payTypeIds);s->map(function($item) {
//            return (string)$item->pay_type_id;
//          })->toArray();
//        }
      }
    }
//    dd($rows->toArray());

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

      $teamIncomeParticular->pay_type_ids = implode(',', $particular['pay_type_ids']);
      $record->teamIncomeParticulars()->save($teamIncomeParticular);

      // save pay type ids
//      $record->save();
      // $payTypeIds = $particular['pay_type_ids'];

//      xxxTeamIncomeParticularPayType::whereTeamIncomeParticularId( $teamIncomeParticular->id )
//        ->whereNotIn('pay_type_id', $payTypeIds)->delete();
//
//      foreach($payTypeIds as $payTypeId) {
//        if(xxxTeamIncomeParticularPayType::whereTeamIncomeParticularId( $teamIncomeParticular->id )
//          ->wherePayTypeId($payTypeId)->count() == 0) {
////          echo 'teamIncomeParticularId = '.$teamIncomeParticular->id; nl();
////          echo 'payTypeId = '.$payTypeIds
//          $payType = xxxTeamIncomeParticularPayType::create([
//            'pay_type_id' => $payTypeId,
//            'team_income_particular_id' => $teamIncomeParticular->id
//          ]);
//        }
//      }
    }
    return response()->json([
      'status'=>true
    ]);
  }
}