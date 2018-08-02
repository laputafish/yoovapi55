<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56BSampleHelper extends IrDataHelper
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
      'SubDate' => isset($sampleForm) ? $sampleForm->application_date : '',
      'SheetNo' => $sheetNo,
      'HKID' => $formEmployee->hkid,

      'TypeOfForm' => $formEmployee->type_of_form,
      'Surname' => $formEmployee->surname,
      'GivenName' => $formEmployee->given_name,
      'NameInChinese' => $formEmployee->name_in_chinese,
      'Sex' => $formEmployee->sex,
      'MartialStatus' => $formEmployee->martial_status,
      'PpNum' => $formEmployee->pp_num,

      // Employee's Spouse
      'SpouseName' => $formEmployee->spouse_name,
      'SpouseHKID' => $formEmployee->spouse_hkid,
      'SpousePpNum' => $formEmployee->spouse_pp_num,

      // Correspondence
      'ResAddr' => $formEmployee->res_addr,
      'AreaCodeResAddr' => $formEmployee->area_code_res_addr,
      'PosAddr' => $formEmployee->pos_addr,

      // Position
      'Capacity' => strtoupper($formEmployee->capacity),
      'PtPrinEmp' => $formEmployee->pt_prin_emp,

      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income Particulars
      // 1. Salary,
      'PerOfSalary' => $formEmployee->per_of_salary,
      'AmtOfSalary' => toCurrency($formEmployee->amt_of_salary),
      // 2. LeavePay,
      'PerOfLeavePay' => $formEmployee->amt_of_leave_pay > 0 ? $formEmployee->per_of_leave_pay : '',
      'AmtOfLeavePay' => toCurrency( $formEmployee->amt_of_leave_pay),
      // 3. DirectorFee,
      'PerOfDirectorFee' => $formEmployee->amt_of_director_fee > 0 ? $formEmployee->per_of_director_fee : '',
      'AmtOfDirectorFee' => $formEmployee->amt_of_director_fee,
      // 4. CommFee,
      'PerOfCommFee' => $formEmployee->amt_of_comm_fee > 0 ? $formEmployee->per_of_comm_fee : '',
      'AmtOfCommFee' => $formEmployee->amt_of_comm_fee,
      // 5. Bonus,
      'PerOfBonus' => $formEmployee->amt_of_bonus > 0 ? $formEmployee->per_of_bonus : '',
      'AmtOfBonus' => $formEmployee->amt_of_bonus,
      // 6. BpEtc,
      'PerOfBpEtc' => $formEmployee->amt_of_bp_etc > 0 ? $formEmployee->per_of_bp_etc : '',
      'AmtOfBpEtc' => $formEmployee->amt_of_bp_etc,
      // 7. PayRetire,
      'PerOfPayRetire' => $formEmployee->amt_of_pay_retire > 0 ? $formEmployee->per_of_pay_retire : '',
      'AmtOfPayRetire' => $formEmployee->amt_of_pay_retire,
      // 8. SalTaxPaid,
      'PerOfSalTaxPaid' => $formEmployee->amt_of_Sal_tax_paid > 0 ? $formEmployee->per_of_Sal_tax_paid : '',
      'AmtOfSalTaxPaid' => $formEmployee->amt_of_Sal_tax_paid,
      // 9. EduBen,
      'PerOfEduBen' => $formEmployee->amt_of_edu_ben > 0 ? $formEmployee->per_of_edu_ben : '',
      'AmtOfEduBen' => $formEmployee->amt_of_edu_ben,
      // 10. GainShareOption,
      'PerOfGainShareOption' => $formEmployee->amt_of_gain_share_option > 0 ? $formEmployee->per_of_gain_share_option : '',
      'AmtOfGainShareOption' => $formEmployee->amt_of_gain_share_option,
      // 11.1
      'NatureOtherRAP1' => $formEmployee->amt_of_other_rap1 > 0 ? $formEmployee->nature_other_rap1 : '',
      'PerOfOtherRAP1' => $formEmployee->amt_of_other_rap1 > 0 ? $formEmployee->per_of_other_rap1 : '',
      'AmtOfOtherRAP1' => $formEmployee->amt_of_other_rap1,
      // 11.2
      'NatureOtherRAP2' => $formEmployee->amt_of_other_rap2 > 0 ? $formEmployee->nature_other_rap2 : '',
      'PerOfOtherRAP2' => $formEmployee->amt_of_other_rap2 > 0 ? $formEmployee->per_of_other_rap2 : '',
      'AmtOfOtherRAP2' => $formEmployee->amt_of_other_rap2,
      // 11.3
      'NatureOtherRAP3' => $formEmployee->amt_of_other_rap3 > 0 ? $formEmployee->nature_other_rap3 : '',
      'PerOfOtherRAP3' => $formEmployee->amt_of_other_rap3 > 0 ? $formEmployee->per_of_other_rap3 : '',
      'AmtOfOtherRAP3' => $formEmployee->amt_of_other_rap3,
      // 12. Pension
      'PerOfPension' => $formEmployee->amt_of_pension > 0 ? $formEmployee->per_of_pension : '',
      'AmtOfPension' => $formEmployee->amt_of_pension,
      
      // total
      'TotalIncome' => toCurrency( $formEmployee->total_income ),

      // Place of Residence
      'PlaceOfResInd' => $formEmployee->place_of_res_ind,

      // Place #1
      'AddrOfPlace1' => $formEmployee->addr_of_place1,
      'NatureOfPlace1' => $formEmployee->nature_of_place1,
      'PerOfPlace1' => $formEmployee->per_of_place1,
      'RentPaidEr1' => $formEmployee->rent_paid_er1,
      'RentPaidEe1' => $formEmployee->rent_paid_ee1,
      'RentRefund1' => $formEmployee->rent_refund1,
      'RentPaidErByEe1' => $formEmployee->rent_paid_er_by_ee1,

      // Place #2
      'AddrOfPlace2' => $formEmployee->addr_of_place2,
      'NatureOfPlace2' => $formEmployee->nature_of_place2,
      'PerOfPlace2' => $formEmployee->per_of_place2,
      'RentPaidEr2' => $formEmployee->rent_paid_er2,
      'RentPaidEe2' => $formEmployee->rent_paid_ee2,
      'RentRefund2' => $formEmployee->rent_refund2,
      'RentPaidErByEe2' => $formEmployee->rent_paid_er_by_ee2,

      // Non-Hong Kong Income
      'OverseaIncInd' => $formEmployee->oversea_inc_ind,
      'AmtPaidOverseaCo' => $formEmployee->amt_paid_oversea_co,
      'NameOfOverseaCo' => $formEmployee->name_of_oversea_co,
      'AddrOfOverseaCo' => $formEmployee->addr_of_oversea_co,

      // Remark
      'Remarks' => $formEmployee->remarks
    ];
    return $result;
  }

}