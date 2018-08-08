<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56gHelper extends IrDataHelper {

  protected static $irdCode = 'IR56F';
//  protected static $testing = true;

  protected static function prepareResult($sheetNo, $formInfo, $employeeInfo, $maritalInfo, $incomeInfo) {
    echo 'prepareResult: '; nf();
    $otherRapsPeriod = '';
    $otherRapsNatures = [];
    $otherRapsAmounts = [];

    // Combine all RAPS
    if(!empty($incomeInfo['NatureOtherRAP1'])) {
      $otherRapsPeriod = $incomeInfo['PerOfOtherRAP1'];
      $otherRapsNatures[] = $incomeInfo['NatureOtherRAP1'];
      $otherRapsAmounts[] = $incomeInfo['AmtOfOtherRAP1'];
      if(!empty($incomeInfo['NatureOtherRAP2'])) {
        $otherRapsNatures[] = $incomeInfo['NatureOtherRAP2'];
        $otherRapsAmounts[] = $incomeInfo['AmtOfOtherRAP2'];
        if (!empty($incomeInfo['NatureOtherRAP3'])) {
          $otherRapsNatures[] = $incomeInfo['NatureOtherRAP3'];
          $otherRapsAmounts[] = $incomeInfo['AmtOfOtherRAP3'];
        }
      }
    }
    $otherRapsNature = implode(', ', $otherRapsNatures);
    $otherRapsAmount = 0;
    foreach($otherRapsAmounts as $value) {
      $otherRapsAmount += $value;
    }

    if($incomeInfo['AmtOfBonus']>0) {
      $otherRapsNature = 'Bonus, ' . $otherRapsNature;
      $otherRapsAmount += $incomeInfo['AmtOfBonus'];
    }

    if($incomeInfo['AmtOfEduBen']>0) {
      $otherRapsNature = 'Education Benefit, '.$otherRapsNature;
      $otherRapsAmount += $incomeInfo['AmtOfEduBen'];
    }

    $spouseHkidPpNum = empty($maritalInfo['SpouseHKID']) ?
      $maritalInfo['SpousePpNum'] :
      $maritalInfo['SpouseHKID'];

    return [
      // Employee's Info
      'SheetNo' => $sheetNo,

      'NameInEnglish' => $employeeInfo['NameInEnglish'],
      'NameInChinese' => $employeeInfo['NameInChinese'],
      'Surname' => $employeeInfo['Surname'],
      'HKID' => $employeeInfo['HKID'],
      'PpNum' => $employeeInfo['PpNum'],
      'Sex' => $employeeInfo['Sex'],

      // Employee's marital status
      'MaritalStatus' => $maritalInfo['MaritalStatus'],
      'SpouseName' => $maritalInfo['SpouseName'],
      'SpouseHKIDPpNum' => $spouseHkidPpNum,

      // Correspondence
      'ResAddr' => $employeeInfo['ResAddr'],
      'PosAddr' => $employeeInfo['PosAddr'],

      // Position
      'Capacity' => $employeeInfo['Capacity'],
      'StartDateOfEmp' => phpDateFormat($formInfo['EmpStartDate'], 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($formInfo['EmpEndDate'], 'd/m/Y'),

      // Income
      // 1. Salary
      'PerOfSalary' => $incomeInfo['PerOfSalary'],
      'AmtOfSalary' => toCurrency($incomeInfo['AmtOfSalary']),
      //
      // 2. LeavePay
      'PerOfLeavePay' => $incomeInfo['PerOfLeavePay'],
      'AmtOfLeavePay' => toCurrency($incomeInfo['AmtOfLeavePay']),
      //
      // 4. CommFee
      'PerOfCommFee' => $incomeInfo['PerOfCommFee'],
      'AmtOfCommFee' => toCurrency($incomeInfo['AmtOfCommFee']),
      //
      // 6. BpEtc
      'PerOfBpEtc' => $incomeInfo['PerOfBpEtc'],
      'AmtOfBpEtc' => toCurrency($incomeInfo['AmtOfBpEtc']),
      //
      // 7. PayRetire
      'PerOfPayRetire' => $incomeInfo['PerOfPayRetire'],
      'AmtOfPayRetire' => toCurrency($incomeInfo['AmtOfPayRetire']),
      //
      // 8. SalTaxPaid
      'PerOfSalTaxPaid' => $incomeInfo['PerOfSalTaxPaid'],
      'AmtOfSalTaxPaid' => toCurrency($incomeInfo['AmtOfSalTaxPaid']),
      //
      // 10. GainShareOption
      'PerOfGainShareOption' => $incomeInfo['PerOfGainShareOption'],
      'AmtOfGainShareOption' => toCurrency($incomeInfo['AmtOfGainShareOption']),
      //
      // 5. Other RAP (Bonus, Rewards, Allowance, etc.)
      'PerOfOtherRAPs' => $otherRapsPeriod,
      'AmtOfOtherRAPs' => toCurrency($otherRapsAmounts),
      //
      // 11.1
      'NatureSpecialPayments' => $incomeInfo['NatureSpecialPayments'],
      'PerOfSpecialPayments' => $incomeInfo['PerOfSpecialPayments'],
      'AmtOfSpecialPayments' => toCurrency($incomeInfo['AmtOfSpecialPayments']),

      // total
      'TotalIncome' => $incomeInfo['TotalIncome'],

      // Employment Status
      'CessationReason' => $employeeInfo['CessationReason'],

      // Place of residence
      'PlaceProvided' => empty($incomeInfo['addrOfPlace']) ? '0': '1',

      'AddrOfPlace' => $incomeInfo['AddrOfPlace1'],
      'NatureOfPlace' => $incomeInfo['NatureOfPlace1'],
      'PerOfPlace' => $incomeInfo['PerOfPlace1'],
      'RentPaidEr' => $incomeInfo['RentPaidEr1'],
      'RentPaidEe' => $incomeInfo['RentPaidEe1'],
      'RentRefund' => $incomeInfo['RentRefund1'],
      'RentPaidErByEe' => $incomeInfo['RentPaidErByEe1'],

      // Non-Hong Kong Income
      'OverseaIncInd' => empty($incomeInfo['AddrOfOverseaCo']) ? '0' : '1',
      'AmtPaidOverseaCo' => toCurrency($incomeInfo['AmtPaidOverseaCo']),
      'NameOfOverseaCo' => $incomeInfo['NameOfOverseaCo'],
      'AddrOfOverseaCo' => $incomeInfo['AddrOfOverseaCo']
    ];
  }

//  protected static function getEmployeeInfo($oaEmployee, $defaults) {
//    echo 'getEmployeeInfo'; nf();
//    $oaSalaries = static::getOASalary();
//    $result = parent::getEmployeeInfo($oaEmployee, $defaults);
//    $result['MonthlyFixedIncome'] = OAEmployeeHelper::getCommencementSalary(
//      phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d'),
//      $oaSalaries
//    );
//    print_r( $result);
//    return $result;
//  }

  protected static function getTestingDefaults() {
    static::$hasDefaults = true;
    return [
      'salary' => 1000,
      'leave_pay' => 2000,
      'director_fee' => 3000,
      'comm_fee' => 4000,
      'bonus' => 5000,
      'bp_etc' => 6000,
      'pay_retire' => 7000,
      'sal_tax_paid' => 8000,
      'edu_ben' => 9000,
      'gain_share_option' => 10000,
      'pension' => 11000,
      'special_payments' => 12000,

      'hkid' => 'C1234561',
      'surname' => 'Chan',
      'givenName' => 'Tai Man',
      'nameInEnglish' => 'Chan, Tai Man',
      'nameInChinese' => '陳大文',
      'phoneNum' => '12345678',
      'ppNum' => 'passport number at hong kong',
      'comRecNameEng' => '(comRecNameEng)',
      'comRecNameChi' => '(comRecNameChi)',
      'sex' => 'M',
      'capacity' => 'CLEAR',
      'ptPrinEmp' => '(ptPrinEmp)',
      'resAddr' => '(resAddr)',
      'areaCodeResAddr' => 'H',
      'posAddr' => 'Same as Above',
      'areaCodePosAddr' => 'K',
      'cessationReason' => 'Leave',

      // Marital
      'spouseName' => '(Spouse Name)',
      'spouseHkid' => '(Spouse HKID)',
      'spousePpNum' => '(Spouse Pp Num)'
    ];
  }
}