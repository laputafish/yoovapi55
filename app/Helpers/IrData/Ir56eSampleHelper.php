<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56eSampleHelper extends IrDataHelper
{

  public static function get($team, $employeeId, $options = [])
  {
    $sampleForm = array_key_exists('form', $options) ? $options['form'] : null;
    $fiscalYearInfo = FormHelper::getFiscalYearInfo($sampleForm);

    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
    $formEmployee = $sampleForm->employees()->whereEmployeeId($employeeId)->first();

    $startDateNumber = $formEmployee->start_date_of_emp;
    $endDateNumber = $formEmployee->end_date_of_emp;

    $empStartDate =
      substr($startDateNumber,0,4).'-'.
      substr($startDateNumber,4,2).'-'.
      substr($startDateNumber,6,2);

    $empEndDate =
      substr($endDateNumber,0,4).'-'.
      substr($endDateNumber, 4, 2).'-'.
      substr($endDateNumber, 6, 2);

    $perOfEmp = $startDateNumber.'-'.$endDateNumber;

    $result = [
      // Employee's Info
      'NameInEnglish' => $formEmployee->surname.', '.$formEmployee->given_name,
      'NameInChinese' => $formEmployee->name_in_chinese,
      'HKID' => $formEmployee->hkid,
      'PpNum' => $formEmployee->pp_num,
      'Sex' => $formEmployee->sex,

      // Employee's marital status
      'MaritalStatus' => $formEmployee->marital_status,
      'SpouseName' => $formEmployee->spouse_name,
      'SpouseHKID' => $formEmployee->spouse_hkid,
      'SpousePpNum' => $formEmployee->spouse_pp_num,

      // Correspondence
      'ResAddr' => $formEmployee->res_addr,
      'PosAddr' => $formEmployee->pos_addr,

      // Position
      'Capacity' => strtoupper($formEmployee->capacity),
      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income
      'MonthlyFixedIncome' => toCurrency($formEmployee->monthly_fixed_income),
      'MonthlyAllowance' => toCurrency($formEmployee->monthly_allowance),
      'FluctuatingIncome' => toCurrency($formEmployee->fluctuating_income),

      // Place #1
      'PlaceProvided' => empty(trim($formEmployee->addr_of_place1)) ? '0' : '1',
      'AddrOfPlace' => $formEmployee->addr_of_place1,
      'NatureOfPlace' => $formEmployee->nature_of_place1,
      'PerOfPlace' => $formEmployee->per_of_place1,
      'RentPaidEr' => $formEmployee->rent_paid_er1,
      'RentPaidEe' => $formEmployee->rent_paid_ee1,
      'RentRefund' => $formEmployee->rent_refund1,
      'RentPaidErByEe' => $formEmployee->rent_paid_er_by_ee1,

      // Non-Hong Kong Income
      'OverseaIncInd' => empty(trim($formEmployee->name_of_oversea_co)) ? '0' : '1',
      'AmtPaidOverseaCo' => $formEmployee->amt_paid_oversea_co,
      'NameOfOverseaCo' => $formEmployee->name_of_oversea_co,
      'AddrOfOverseaCo' => $formEmployee->addr_of_oversea_co,

      // share option
      'ShareBeforeEmp' => $formEmployee->share_before_emp
    ];
    return $result;
  }

}