<?php namespace App\Helpers\IrData;

use App\Helpers\IrData\Ir56b;
use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;
use App\Helpers\OA\OASalaryHelper;
use App\Helpers\OA\OAPayslipHelper;

class IrDataHelper
{
  protected static $team = null;
  protected static $employeeId = null;
  protected static $form = null;
  protected static $oaAuth = null;

//  public static function getIr56b($form, $employeeId)
//  {
//    $team = $form->team;
//    $oaAuth = OAHelper::refreshTokenByTeam($team);
//
//    $oaEmployee = oaEmployeeHelper::get($employeeId, $oaAuth, $team->oa_team_id);
//    $oaTeam = oaTeamHelper::get($team->oa_team_id, $oaAuth);
//
//    $irData = new ir56b;
//    $employerFileNo = $team->getSetting('employer_file_no', '');
//    $employerFileNoSegs = explode('-', $employerFileNo);
//
//    $irData->section = count($employerFileNoSegs)>0 ? $employerFileNoSegs[0] : '';
//    $irData->ern = count($employerFileNoSegs)>1 ? $employerFileNoSegs[1] : '';
//    $irData->yrErReturn = 0;
//    $irData->subDate = date('Ymd');
//    $irData->erName = $oaTeam['name'];
//    $irData->designation = '';
//    $irData->noRecordBatch = 0;
//    $irData->totIncomeBatch = 0;
//    $irData->employees = [];
//
//    dd((array)$irData);
//
//    dd($oaEmployee);
//
//  }

  public static function getOAEmployee()
  {
    return OAEmployeeHelper::get(self::$oaAuth, self::$employeeId, self::$team->oa_team_id);
  }

  public static function getOAAdminEmployee()
  {
    return OAEmployeeHelper::getAdminInfo(self::$oaAuth, self::$employeeId, self::$team->oa_team_id);
  }

  public static function getOAPayrollSummary($period)
  {
    // dd($period);
    $payslips = OAPayslipHelper::get(self::$oaAuth, self::$employeeId, self::$team->oa_team_id);
    $effectivePayslips = [];

    foreach($payslips as $payslip) {
      if(inBetween($payslip['startedDate'], $period) ||
        inBetween($payslip['endedDate'], $period)) {
        $effectivePayslips[] = $payslip;
      }
    }

    $incomeParticulars = self::$team->incomeParticulars()->with('incomeParticular')->get();

//    dd($incomeParticulars->toArray());
    $incomeSummary = [];
    foreach( $incomeParticulars as $item ) {
      $incomeSummary[$item['income_particular']['token']] = [];

    }

    $summary = [
      'totalIncome' => 0,
      'amtOfSalary' => 0, // 1.
      'amtOfLeavePay' => 0, // 2
      'amtOfDirectorFee' => 0, // 3
      'amtOfCommFee' => 0, // 4
      'amtOfBonus' => 0, // 5
      'amtOfBpEtc' => 0, // 6
      'amtOfPayRetire' => 0, // 7
      'amtOfSalTaxPaid' => 0, // 8
      'amtOfEduBen' => 0, // 9
      'amtOfGainShareOption' => 0, // 10
      'otherRaps' => [],
      'amtOfPension' => 0
    ];

    $summary['otherRaps'] = [
      ['nature'=>'Rewards', 'amt'=>1000],
      ['nature'=>'Allowance', 'amt'=>2000]
    ];

    return $summary;
  }

  public static function getOATeam() {
    return OATeamHelper::get(self::$oaAuth, self::$team->oa_team_id);
  }

  public static function getOASalary() {
    return OASalaryHelper::get(self::$oaAuth, self::$employeeId, self::$team->oa_team_id);
  }
}