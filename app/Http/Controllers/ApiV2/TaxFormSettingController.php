<?php namespace App\Http\Controllers\ApiV2;

use App\Models\IncomeParticular;
use App\Models\TeamIncomeParticular;
use App\Models\xxxTeamIncomeParticularPayType;
use App\Models\Team;

class TaxFormSettingController extends BaseAuthController
{
  protected $modelName = 'IncomeParticular';

  public function index()
  {
    $oaTeamId = \Input::get('teamId', '');
    $team = Team::whereOaTeamId($oaTeamId)->first();

    $rows = IncomeParticular::orderby('seq_no')->get();
    foreach ($rows as $row) {
      $teamIncomeParticular = $row->teamIncomeParticulars()->whereTeamId($oaTeamId)->first();
      $row->pay_type_ids = [];
      if (isset($teamIncomeParticular)) {
        $payTypeIds = trim($teamIncomeParticular->pay_type_ids);
        $row->pay_type_ids = empty($payTypeIds) ? [] : explode(',', $payTypeIds);
      }
    }
    $lang = $team->getSetting('lang', 'en-us');
    $data = [
      'income_particulars' => $rows->toArray(),
      'lang' => $lang
    ];
    return response()->json([
      'status' => true,
      'result' => $data
    ]);
  }
}