<?php namespace App\Helpers\IrData;

class Ir56gTestHelper extends IrBaseTestHelper {
  public static function get($langCode) {
    $isEnglish = $langCode == 'en-us';
    $empStartDate = '2017-04-01';
    $empEndDate = '2018-03-31';
    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    return [
      // Employee's Info
      'SheetNo' => 1,

      'NameInEnglish' => 'Chan, Tai Man',
      'NameInChinese' => '陳大文',
      'Surname' => 'Chan',
      'HKID' => 'C1234567',
      'PpNum' => '(Passport Number and issed at Hong Kong)',
      'Sex' => 'M',
      'LeftAtYear' => '2018',
      'LeftAtMonth' => '01',
      'LeftAtDay' => '20',

      // Employee's marital status
      'MaritalStatus' => '1',
      'SpouseName' => '(Spouse Name)',
      'SpouseHKIDPpNum' => '(Spouse HKID/PpNum)',

      // Correspondence
      'ResAddr' => '1/F., 1st First Street, Kwun Tong.',
      'PosAddr' => $isEnglish ? 'Same as Above' : '同上',

      // Position
      'Capacity' => 'ACCOUNTANT',
      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income
      // 1. Salary
      'PerOfSalary' => $perOfEmp,
      'AmtOfSalary' => toCurrency(1000),
      //
      // 2. LeavePay
      'PerOfLeavePay' => $perOfEmp,
      'AmtOfLeavePay' => toCurrency(2000),
      //
      // 4. CommFee
      'PerOfCommFee' => $perOfEmp,
      'AmtOfCommFee' => toCurrency(3000),
      //
      // 6. BpEtc
      'PerOfBpEtc' => $perOfEmp,
      'AmtOfBpEtc' => toCurrency(4000),
      //
      // 7. PayRetire
      'PerOfPayRetire' => $perOfEmp,
      'AmtOfPayRetire' => toCurrency(5000),
      //
      // 8. SalTaxPaid
      'PerOfSalTaxPaid' => $perOfEmp,
      'AmtOfSalTaxPaid' => toCurrency(6000),
      //
      // 10. GainShareOption
      'PerOfGainShareOption' => $perOfEmp,
      'AmtOfGainShareOption' => toCurrency(7000),
      //
      // 5. Other RAP (Bonus, Rewards, Allowance, etc.)
      'PerOfOtherRAPs' => $perOfEmp,
      'AmtOfOtherRAPs' => toCurrency(8000),
      //
      // 11.1
      'NatureSpecialPayments' => '(Nature)',
      'PerOfSpecialPayments' => $perOfEmp,
      'AmtOfSpecialPayments' => toCurrency(9000),

      // total
      'TotalIncome' => toCurrency(45000),

      // Employment Status
      'CessationReason' => 'Self-leave',

      // Place of residence
      'PlaceProvided' => '1',

      'AddrOfPlace' => '1/F., 1st First Street, Kwun Tong',
      'NatureOfPlace' => 'Flat',
      'PerOfPlace' => $perOfEmp,
      'RentPaidEr' => '0',
      'RentPaidEe' => 120000,
      'RentRefund' => 120000,
      'RentPaidErByEe' => '0',

      // Non-Hong Kong Income
      'OverseaIncInd' => '1',
      'AmtPaidOverseaCo' => toCurrency(10000),
      'NameOfOverseaCo' => 'Oversea Company Ltd.',
      'AddrOfOverseaCo' => '1/F., First Bldg., Oversea Street, Oversea.'
    ];
  }


}