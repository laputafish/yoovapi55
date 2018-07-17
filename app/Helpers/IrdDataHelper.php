<?php namespace App\Helpers;

use App\Helpers\IrData\Ir56b;
use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;

class IrdDataHelper
{
  public static function getIr56b($form, $formEmployee)
  {
    $team = $form->team;
    OAHelper::updateTeamToken($team);
    $oaAuth = $team->getOaAuth();

    $oaEmployee = oaEmployeeHelper::get($formEmployee->employee_id, $oaAuth, $team->oa_team_id);
    $oaTeam = oaTeamHelper::get($team->oa_team_id, $oaAuth);

    $irData = new ir56b;
    $employerFileNo = $team->getSetting('employer_file_no', '');
    $employerFileNoSegs = explode('-', $employerFileNo);

    $irData->section = count($employerFileNoSegs)>0 ? $employerFileNoSegs[0] : '';
    $irData->ern = count($employerFileNoSegs)>1 ? $employerFileNoSegs[1] : '';
    $irData->yrErReturn = 0;
    $irData->subDate = date('Ymd');
    $irData->erName = $oaTeam['name'];
    $irData->designation = '';
    $irData->noRecordBatch = 0;
    $irData->totIncomeBatch = 0;
    $irData->employees = [];

    dd((array)$irData);

    dd($oaEmployee);

  }

  public static function getIr56e($form, $formEmployee)
  {
    $team = $form->team;
    OAHelper::updateTeamToken($team);
    $oaAuth = $team->getOaAuth();



  }
}