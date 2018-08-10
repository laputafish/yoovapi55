<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56fSampleHelper extends IrDataHelper
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

    $spouseHkidPpNum = empty($formEmployee->spouse_hkid) ? $formEmployee->spouse_pp_num : $formEmployee->spouse_hkid;

    $amtOfOtherRaps =  $formEmployee->amt_of_director_fee +
      $formEmployee->amt_of_bonus +
      $formEmployee->amt_of_edu_ben;

    $totalIncome =
      $formEmployee->amt_of_salary +
      $formEmployee->amt_of_leave_pay +
      $formEmployee->amt_of_comm_fee +
      $formEmployee->amt_of_bp_etc +
      $formEmployee->amt_of_pay_retire +
      $formEmployee->amt_of_sal_tax_paid +
      $formEmployee->amt_of_gain_share_option +
      $amtOfOtherRaps +
      $formEmployee->amt_of_other_rap2;

    $result = [
      'sheetNo' => $sheetNo,

      // Employee's Info
      'NameInEnglish' => $formEmployee->surname.', '.$formEmployee->given_name,
      'NameInChinese' => $formEmployee->name_in_chinese,
      'Surname' => $formEmployee->surname,
      'HKID' => $formEmployee->hkid,
      'PpNum' => $formEmployee->pp_num,
      'Sex' => $formEmployee->sex,

      // Employee's marital status
      'MaritalStatus' => $formEmployee->marital_status,
      'SpouseName' => $formEmployee->spouse_name,
      'SpouseHKIDPpNum' => $spouseHkidPpNum,

      // Correspondence
      'ResAddr' => $formEmployee->res_addr,
      'PosAddr' => $formEmployee->pos_addr,

      // Position
      'Capacity' => strtoupper($formEmployee->capacity),
      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income Particulars
      // 1. Salary,
      'PerOfSalary' => $formEmployee->per_of_salary,
      'AmtOfSalary' => toCurrency($formEmployee->amt_of_salary),
      //
      // 2. LeavePay,
      'PerOfLeavePay' => $formEmployee->amt_of_leave_pay > 0 ? $formEmployee->per_of_leave_pay : '',
      'AmtOfLeavePay' => toCurrency( $formEmployee->amt_of_leave_pay),
      //
      // 3. CommFee,
      'PerOfCommFee' => $formEmployee->amt_of_comm_fee > 0 ? $formEmployee->per_of_comm_fee : '',
      'AmtOfCommFee' => toCurrency($formEmployee->amt_of_comm_fee),
      //
      // 4. BpEtc,
      'PerOfBpEtc' => $formEmployee->amt_of_bp_etc > 0 ? $formEmployee->per_of_bp_etc : '',
      'AmtOfBpEtc' => toCurrency($formEmployee->amt_of_bp_etc),
      //
      // 5. PayRetire,
      'PerOfPayRetire' => $formEmployee->amt_of_pay_retire > 0 ? $formEmployee->per_of_pay_retire : '',
      'AmtOfPayRetire' => toCurrency($formEmployee->amt_of_pay_retire),
      //
      // 6. SalTaxPaid,
      'PerOfSalTaxPaid' => $formEmployee->amt_of_Sal_tax_paid > 0 ? $formEmployee->per_of_Sal_tax_paid : '',
      'AmtOfSalTaxPaid' => toCurrency($formEmployee->amt_of_Sal_tax_paid),
      //
      // 7. GainShareOption,
      'PerOfGainShareOption' => $formEmployee->amt_of_gain_share_option > 0 ? $formEmployee->per_of_gain_share_option : '',
      'AmtOfGainShareOption' => toCurrency($formEmployee->amt_of_gain_share_option),
      //
      // 8. Other RAP (Bonus, Rewards, Allowance, etc.)
      'PerOfOtherRAPs' => $amtOfOtherRaps > 0 ? $formEmployee->per_of_other_rap1 : '',
      'AmtOfOtherRAPs' => toCurrency($amtOfOtherRaps),

      // 9
      'PerOfSpecialPayments' => $formEmployee->amt_of_other_rap2 > 0 ? $formEmployee->per_of_other_rap2 : '',
      'AmtOfSpecialPayments' => toCurrency($formEmployee->amt_of_other_rap2),
      'NatureSpecialPayments' => $formEmployee->amt_of_other_rap2 > 0 ? $formEmployee->nature_other_rap2 : '',

      // total
      'TotalIncome' => toCurrency($totalIncome),

      // Employment Status
      'CessationReason' => $formEmployee->cessation_reason,

      // Place of Residence
      'PlaceProvided' => empty($formEmployee->addr_of_place1) ? '0' : '1',

      // Place #1
      'AddrOfPlace' => $formEmployee->addr_of_place1,
      'NatureOfPlace' => $formEmployee->nature_of_place1,
      'PerOfPlace' => $formEmployee->per_of_place1,
      'RentPaidEr' => toCurrency($formEmployee->rent_paid_er1),
      'RentPaidEe' => toCurrency($formEmployee->rent_paid_ee1),
      'RentRefund' => toCurrency($formEmployee->rent_refund1),
      'RentPaidErByEe' => toCurrency($formEmployee->rent_paid_er_by_ee1),

      // Non-Hong Kong Income
      'OverseaIncInd' => $formEmployee->oversea_inc_ind,
      'AmtPaidOverseaCo' => toCurrency($formEmployee->amt_paid_oversea_co),
      'NameOfOverseaCo' => $formEmployee->name_of_oversea_co,
      'AddrOfOverseaCo' => $formEmployee->addr_of_oversea_co
    ];
    return $result;
  }

}