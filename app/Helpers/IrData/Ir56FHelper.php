<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56FHelper extends IrDataHelper {

  protected static $irdCode = 'IR56F';

  protected static function prepareResult($sheetNo, $formInfo, $employeeInfo, $maritalInfo, $incomeInfo) {
    $otherRapsNatures = [];
    $otherRapsAmounts = [];

    if(!empty($incomeInfo['NatureOtherRAP1'])) {
      $otherRapsPeriod = $incomeInfo['PerOfOtherRAP1'];
      $otherRapsNatures[] = $incomeInfo['NatureOtherRAP1'];
      $otherRapsAmount[] = $incomeInfo['AmtOfOtherRAP1'];
      if(!empty($incomeInfo['NatureOtherRAP2'])) {
        $otherRapsNatures[] = $incomeInfo['NatureOtherRAP2'];
        $otherRapsAmount[] = $incomeInfo['AmtOfOtherRAP2'];
        if (!empty($incomeInfo['NatureOtherRAP3'])) {
          $otherRapsNatures[] = $incomeInfo['NatureOtherRAP3'];
          $otherRapsAmount[] = $incomeInfo['AmtOfOtherRAP3'];
        }
      }
    }

    $otherRapsNature = implode(', ', $otherRapsNatures);
    $otherRapsAmount = array_reduce($otherRapsAmounts, function($carry, $item) {
      $carry += $item;
      return $carry;
    });

    return [
      // Employee's Info
      'SheetNo' => $sheetNo,

      'NameInEnglish' => $employeeInfo['NameInEnglish'],
      'NameInChinese' => $employeeInfo['NameInChinese'],
      'HKID' => $employeeInfo['HKID'],
      'PpNum' => $employeeInfo['PpNum'],
      'Sex' => $employeeInfo['Sex'],

      // Employee's marital status
      'MaritalStatus' => $maritalInfo['MaritalStatus'],
      'SpouseName' => $maritalInfo['SpouseName'],
      'SpouseHKID' => $maritalInfo['SpouseHKID'],
      'SpousePpNum' => $maritalInfo['SpousePpNum'],

      // Correspondence
      'ResAddr' => $employeeInfo['ResAddr'],
      'PosAddr' => $employeeInfo['PosAddr'],

      // Position
      'Capacity' => $employeeInfo['Capacity'],
      'StartDateOfEmp' => phpDateFormat($formInfo['EmpStartDate'], 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($formInfo['EmpEndDate'], 'd/m/Y'),
      'MonthlyFixedIncome' => toCurrency($incomeInfo['MonthlyFixedIncome']),
      'MonthlyAllowance' => toCurrency($incomeInfo['MonthlyAllowance']),
      'FluctuatingIncome' => toCurrency($incomeInfo['FluctuatingIncome']),

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
      // 5. BonusEduBen
      'PerOfBonusEduBen' => $incomeInfo['PerOfBonusEduBen'],
      'AmtOfBonusEduBen' => toCurrency($incomeInfo['AmtOfBonus'] + $incomeInfo['AmtOfEduBen']),
      //
      // 11.1
      'NatureOtherRAP' => $otherRapsNature,
      'PerOfOtherRAP' => $otherRapsPeriod,
      'AmtOfOtherRAP' => $otherRapsAmount,

      // total
      'TotalIncome' => $incomeInfo['AmtOfSalary'] +
        $incomeInfo['AmtOfLeavePay'] +
        $incomeInfo['AmtOfCommFee'] +
        $incomeInfo['AmtOfBpEtc'] +
        $incomeInfo['AmtOfPayRetire'] +
        $incomeInfo['AmtOfSalTaxPaid'] +
        $incomeInfo['AmtOfGainShareOption'] +
        $incomeInfo['AmtOfBonusEduBen'] +
        $otherRapsAmount,

      // Employment Status
      'CessationReason' => $employeeInfo['CessationReason'],

      // Place of residence
      'PlaceProvided' => empty($incomeInfo['addrOfPlace']) ? '0': '1',

      'AddrOfPlace' => $incomeInfo['AddrOfPlace'],
      'NatureOfPlace' => $incomeInfo['NatureOfPlace'],
      'PerOfPlace' => $incomeInfo['PerOfPlace1'],
      'RentPaidEr' => $incomeInfo['RentPaidEr1'],
      'RentPaidEe' => $incomeInfo['RentPaidEe1'],
      'RentRefund' => $incomeInfo['RentRefund'],
      'RentPaidErByEe' => $incomeInfo['RentPaidErByEe'],

      // Non-Hong Kong Income
      'OverseaIncInd' => empty($incomeInfo['AddrOfOverseaCo']) ? '0' : '1',
      'AmtPaidOverseaCo' => toCurrency($incomeInfo['AmtPaidOverseaCo']),
      'NameOfOverseaCo' => $incomeInfo['NameOfOverseaCo'],
      'AddrOfOverseaCo' => $incomeInfo['AddrOfOverseaCo']
    ];
  }

  protected static function getEmployeeInfo($oaEmployee, $defaults) {
    echo 'getEmployeeInfo'; nf();
    $oaSalaries = self::getOASalary();
    $result = parent::getEmployeeInfo($oaEmployee, $defaults);
    $result['MonthlyFixedIncome'] = OAEmployeeHelper::getCommencementSalary(
      phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d'),
      $oaSalaries
    );
    return
  }
}