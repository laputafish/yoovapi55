<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56MSampleHelper extends IrDataHelper
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
      'SheetNo' => $sheetNo,
      'ComRecNameEng' => '',
      'ComRecNameChi' => '',
      'ComRecBRN' => '',

      // Employee Info
      'NameInEnglish' => $formEmployee->surname.', '.$formEmployee->given_name,
      'NameInChinese' => $formEmployee->name_in_chinese,
      'HKID' => $formEmployee->hkid,
      'Sex' => $formEmployee->sex,

//      'TypeOfForm' => $formEmployee->type_of_form,
//      'Surname' => $formEmployee->surname,
//      'GivenName' => $formEmployee->given_name,
//      'PpNum' => $formEmployee->pp_num,
//      'SubDate' => isset($sampleForm) ? $sampleForm->application_date : '',

      // Employee's Spouse
      'MaritalStatus' => $formEmployee->marital_status,
      'SpouseName' => $formEmployee->spouse_name,
      'SpouseHKID' => $formEmployee->spouse_hkid,
      'SpousePpNum' => $formEmployee->spouse_pp_num,

      // Correspondence
      'PosAddr' => $formEmployee->pos_addr,
      'AreaCodePosAddr' => $formEmployee->area_code_res_addr,
      'PhoneNum' => $formEmployee->phone_num,

      // Position
      'Capacity' => strtoupper($formEmployee->capacity),

      'StartDateOfService' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfService' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income Particulars

      // Type 1
      'PerOfType1' => $formEmployee->per_of_salary,
      'AmtOfType1' => toCurrency($formEmployee->amt_of_salary),

      // Type 2
      'PerOfType2' => $formEmployee->amt_of_leave_pay > 0 ? $formEmployee->per_of_leave_pay : '',
      'AmtOfType2' => toCurrency( $formEmployee->amt_of_leave_pay),

      // Type 3
      'PerOfType3' => $formEmployee->amt_of_director_fee > 0 ? $formEmployee->per_of_director_fee : '',
      'AmtOfType3' => toCurrency($formEmployee->amt_of_director_fee),

      // Artist Fee
      'PerOfArtistFee' => $formEmployee->amt_of_comm_fee > 0 ? $formEmployee->per_of_comm_fee : '',
      'AmtOfArtistFee' => toCurrency($formEmployee->amt_of_comm_fee),

      // Copyright
      'PerOfCopyright' => $formEmployee->amt_of_bonus > 0 ? $formEmployee->per_of_bonus : '',
      'AmtOfCopyright' => toCurrency($formEmployee->amt_of_bonus),

      // Consult Fee
      'PerOfConsultFee' => $formEmployee->amt_of_bp_etc > 0 ? $formEmployee->per_of_bp_etc : '',
      'AmtOfConsultFee' => toCurrency($formEmployee->amt_of_bp_etc),

      // Other Inc1
      'PerOfOtherInc1' => $formEmployee->amt_of_pay_retire > 0 ? $formEmployee->per_of_pay_retire : '',
      'AmtOfOtherInc1' => toCurrency($formEmployee->amt_of_pay_retire),
      'NatureOtherInc1' => 'Service Fees',

      // Other Inc2
      'PerOfOtherInc2' => $formEmployee->amt_of_Sal_tax_paid > 0 ? $formEmployee->per_of_Sal_tax_paid : '',
      'AmtOfOtherInc2' => toCurrency($formEmployee->amt_of_Sal_tax_paid),
      'NatureOtherInc2' => 'Maintenance Fee',

      // total
      'TotalIncome' => toCurrency(
        $formEmployee->amt_of_salary +
        $formEmployee->amt_of_leave_pay +
        $formEmployee->amt_of_director_fee +
        $formEmployee->amt_of_comm_fee +
        $formEmployee->amt_of_bonus +

        $formEmployee->amt_of_bp_etc +
        $formEmployee->amt_of_pay_retire +
        $formEmployee->amt_of_Sal_tax_paid
      ),


      'IndOfSumWithheld' => $formEmployee->amt_of_sum_withheld > 0 ? '1' : '0',
      'AmtOfSumWithheld' => toCurrency($formEmployee->amt_of_sum_withheld),

      // Remark
      'IndOfRemark' => empty($formEmployee->remarks) ? '0' : '1',
      'Remarks' => $formEmployee->remarks
    ];
    return $result;
  }

}