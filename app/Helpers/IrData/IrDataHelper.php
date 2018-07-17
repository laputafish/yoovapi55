<?php namespace App\Helpers\IrData;

use App\Helpers\IrData\Ir56b;
use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;
use App\Helpers\OA\OASalaryHelper;

class IrDataHelper
{
  protected static $team = null;
  protected static $form = null;
  protected static $formEmployee = null;
  protected static $oaAuth = null;

  public static function getIr56b($form, $formEmployee)
  {
    $team = $form->team;
    $oaAuth = OAHelper::refreshTokenByTeam($team);

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

  public static function getOAEmployee()
  {
    return oaEmployeeHelper::get(self::$formEmployee->employee_id, self::$oaAuth, self::$teamId);
  }

  public static function getOAAdminEmployee()
  {
    return oaEmployeeHelper::getAdminInfo(self::$formEmployee->employee_id, self::$oaAuth, self::$team->oa_team_id);
  }

  public static function getOATeam() {
    return oaTeamHelper::get(self::$team->oa_team_id, self::$oaAuth);
  }

  public static function getOASalary() {
    return oaSalaryHelper::get(self::$formEmployee->employee_id, self::$oaAuth, self::$team->oa_team_id);
  }
}