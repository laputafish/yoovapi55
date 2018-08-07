<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Ir56bIncome;
use App\Models\TeamIr56bIncome;
use App\Models\xxxTeamIncomeParticularPayType;

class Ir56bIncomeController extends BaseAuthController {
  protected $modelName = 'Ir56bIncome';

  public function index() {
    $teamId = \Input::get('teamId', '');
    $rows = Ir56bIncome::orderby('seq_no')->get();
    foreach( $rows as $row ) {
      $teamIr56bIncome = $row->teamIr56bIncomes()->whereTeamId($teamId)->first();
      $row->pay_type_ids = [];
      if (isset($teamIr56bIncome)) {
        $payTypeIds = trim($teamIr56bIncome->pay_type_ids);
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
    $ir56bIncomes = \Input::get('ir56bIncomes');
    foreach($ir56bIncomes as $particular) {
      $particularId = $particular['id'];
      $record = Ir56bIncome::find($particularId);

      $teamIr56bIncome = TeamIr56bIncome::whereTeamId($teamId)->whereIr56bIncomeId($particularId)->first();
      if(is_null($teamIr56bIncome)) {
        $teamIr56bIncome = TeamIr56bIncome::create([
          'team_id' => $teamId
        ]);
      }

      $teamIr56bIncome->pay_type_ids = implode(',', $particular['pay_type_ids']);
      $record->teamIr56bIncomes()->save($teamIr56bIncome);
    }
    return response()->json([
      'status'=>true
    ]);
  }
}