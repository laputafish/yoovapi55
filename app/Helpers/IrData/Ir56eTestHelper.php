<?php namespace App\Helpers\IrData;

class Ir56eTestHelper extends IrBaseTestHelper {
  public static function get($langCode) {
    $isEnglish = $langCode == 'en-us';
    $empStartDate = '2017-04-01';
    $empEndDate = '2018-03-31';
    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    return [
      // Employee's Info
      'NameInEnglish' => 'Chan, Tai Man',
      'NameInChinese' => '陳大文',
      'HKID' => 'C1234567',
      'PpNum' => '(Passport Number and issed at Hong Kong)',
      'Sex' => 'M',

      // Employee's marital status
      'MaritalStatus' => '1',
      'SpouseName' => '(Spouse Name)',
      'SpouseHKID' => 'A1234567',
      'SpousePpNum' => '{Passport No.}',

      // Correspondence
      'ResAddr' => '1/F., 1st First Street, Kwun Tong.',
      'PosAddr' => $isEnglish ? 'Same as Above' : '同上',

      // Position
      'Capacity' => 'ACCOUNTANT',
      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income
      'MonthlyFixedIncome' => 20000,
      'MonthlyAllowance' => 10000,
      'FluctuatingIncome' => 5000,

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
      'AddrOfOverseaCo' => '1/F., First Bldg., Oversea Street, Oversea.',

      // share option
      'ShareBeforeEmp' => '1'
    ];
  }


}