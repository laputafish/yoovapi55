<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Ir56bIncome;
use App\Models\Ir56fIncome;
use App\Models\TeamIr56bIncome;
use App\Models\Team;
use App\Models\Lang;

class TaxFormSettingController extends BaseAuthController
{
  public function index()
  {
    $oaTeamId = \Input::get('teamId', '');
    $team = Team::whereOaTeamId($oaTeamId)->first();

    // Income Particulars (IR56B)
    $ir56bIncomes = Ir56bIncome::orderby('seq_no')->get();
    foreach ($ir56bIncomes as $row) {
      $teamIr56bIncome = $row->teamIr56bIncomes()->whereTeamId($oaTeamId)->first();
      $row->pay_type_ids = [];
      if (isset($teamIr56bIncome)) {
        $payTypeIds = trim($teamIr56bIncome->pay_type_ids);
        $row->pay_type_ids = empty($payTypeIds) ? [] : explode(',', $payTypeIds);
      }
    }

    // Incomes (IR56F)
    $ir56fIncomes = Ir56fIncome::orderby('seq_no')->get();
    foreach ($ir56fIncomes as $row) {
      $teamIr56fIncome = $row->teamIr56fIncomes()->whereTeamId($oaTeamId)->first();
      $row->pay_type_ids = [];
      if (isset($teamIr56fIncome)) {
        $payTypeIds = trim($teamIr56fIncome->pay_type_ids);
        $row->pay_type_ids = empty($payTypeIds) ? [] : explode(',', $payTypeIds);
      }
    }

    // File No.
    $fileNo = $team->getSetting('fileNo', '');

    // Languages
    $langCode = $team->getSetting('lang', 'en-us');
    $lang = Lang::whereCode($langCode)->first();

    $data = [
      'ir56b_incomes' => $ir56bIncomes->toArray(),
      'ir56f_incomes' => $ir56fIncomes->toArray(),
      'fileNo' => $fileNo,
      'langId' => $lang->id,
      'designation' => $team->getSetting('designation', ''),
      'signatureName' => $team->getSetting('signatureName', '')
    ];

    return response()->json([
      'status' => true,
      'result' => $data
    ]);
  }
}