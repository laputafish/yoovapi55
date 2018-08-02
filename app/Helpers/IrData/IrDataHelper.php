<?php namespace App\Helpers\IrData;

use App\Helpers\IrData\Ir56b;
use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\OA\OATeamHelper;
use App\Helpers\OA\OASalaryHelper;
use App\Helpers\OA\OAPayslipHelper;

use App\Models\IrdForm;

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

  public static function getIrdInfo($irdCode, $langCode, $extra=[]) {
    $irdForm = IrdForm::whereIrdCode($irdCode)->whereEnabled(1)->first();
    $irdFormFile = $irdForm->getFile($langCode);

    return array_merge([
      'langCode'=>$langCode,
      'irdForm'=>$irdForm,
      'fields'=>$irdFormFile->fields->toArray(),
      'is_sample'=>false
    ], $extra);
  }

  public static function getOAPayrollSummary($fiscalYearInfo)
  {
    $period = [
      'startDate' => $fiscalYearInfo['startDate'],
      'endDate' => $fiscalYearInfo['endDate']
    ];

    $summary = [
      'totalIncome'=>0,
      'salary'=>0, // 1
      'leave_pay'=>0, // 2
      'director_fee'=>0, // 3
      'comm_fee'=>0, // 4
      'bonus'=>0, // 5
      'bp_etc'=>0, // 6
      'pay_retire'=>0, // 7
      'sal_tax_paid'=>0, // 8
      'edu_ben'=>0, // 9
      'gain_share_option'=>0, // 10
      'other_raps'=>[], // 11
      'pension'=>0 // 12
    ];
    // dd($period);

    // Fetch pay type mapping
    //
    // $payTypeMappings = [
    //  'salary' => [1,2,4],
    //  'leave_pay' => [2,4],
    //  ...
    // ]
    //
    // Init all summary item to 0;
    //
    $teamIncomeParticulars = self::$team->incomeParticulars()->with('incomeParticular')->get();
    $payTypeToTokenMappings = [];
    $summary['totalIncome'] = 0;
    // $otherRapPayTypeIds = [];
    $otherRaps = [];
    // $otherRaps = [
    //    [
    //      'nature'=>'xxx',
    //      'amt'=>0
    //    ],
    //    [
    //      'nature'=>'xxx',
    //      'amt'=>0
    //    ]
    // ]
    foreach( $teamIncomeParticulars as $item ) {
      $token = $item->incomeParticular->token;
      $payTypeIds = trim($item->pay_type_ids);
      if($payTypeIds != '') {
        $arPayTypeIds = explode(',', $payTypeIds);
        foreach( $arPayTypeIds as $payTypeId) {
          $payTypeToTokenMappings['payType_' . $payTypeId] = $token;
//          if($token == 'other_raps') {
//            $otherRapPayTypeIds[] = $payTypeId;
//          }
        }
      }
      if($token == 'other_raps') {
        $summary[$token] = [];
      } else {
        $summary[$token] = 0;
      }
    }

    //****************************************
    // Fetch payslips amount of each pay type
    //****************************************

    // Filter payslips for related fiscal year
    $payslips = OAPayslipHelper::get(self::$oaAuth, self::$employeeId, self::$team->oa_team_id);

    $effectivePayslips = [];
    foreach($payslips as $i=>$payslip) {
//      echo 'i='.$i.' (start:'.$payslip['startedDate'].' to '.$payslip['endedDate'].')'; nf();
      if(inBetween($payslip['startedDate'], $period) ||
        inBetween($payslip['endedDate'], $period)) {
        $effectivePayslips[] = $payslip;
      }
    }

    foreach($effectivePayslips as $payslip) {
      foreach( $payslip['details'] as $detail ) {
        if($detail['isBasicSalary']) {
          $summary['salary'] += $detail['amount'];
          $summary['totalIncome'] += $detail['amount'];
        } else {
          if($detail['payTypeId']) {
            $index = 'payType_'.$detail['payTypeId'];
            if(array_key_exists($index, $payTypeToTokenMappings)) {
              $token = $payTypeToTokenMappings['payType_' . $detail['payTypeId']];
              if($token == 'other_raps') {
                 if(array_key_exists($detail['name'], $otherRaps)) {
                   $otherRaps[$detail['name']] += $detail['amount'];
                 } else {
                   $otherRaps[$detail['name']] = $detail['amount'];
                 }
              } else {
                $summary[$token] += $detail['amount'];
              }
              $summary['totalIncome'] += $detail['amount'];
            }
          }
        }

      }
    }

    foreach($otherRaps as $nature=>$amount) {
      $summary['other_raps'][] = [
        'nature' => $nature,
        'amt' => $amount
      ];
    }
    return $summary;
  }

  public static function getOATeam() {
    return OATeamHelper::get(self::$oaAuth, self::$team->oa_team_id);
  }

  public static function getOASalary() {
    return OASalaryHelper::get(self::$oaAuth, self::$employeeId, self::$team->oa_team_id);
  }
}