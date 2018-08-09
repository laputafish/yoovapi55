<?php namespace App\Helpers\IrData;

class Ir56mTestHelper extends IrBaseTestHelper {
  public static function get($langCode) {
    $isEnglish = $langCode == 'en-us';
    $empStartDate = '2017-04-01';
    $empEndDate = '2018-03-31';
    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    return [
      // Employee's Info
      'SheetNo' => 1,
      'ComRecNameEng' => '{ComRecNameEng}',
      'ComRecNameChi' => 'ComRecNameChi}',
      'ComRecBRN' => '{ComRecBRN}',

      'NameInEnglish' => 'Chan, Tai Man',
      'NameInChinese' => '陳大文',
      'HKID' => 'C1234567',
      'Sex' => 'M',

      // Employee's marital status
      'MaritalStatus' => '1',
      'SpouseName' => '{Spouse Name}',
      'SpouseHKID' => '{Spouse HKID}',
      'SpousePpNum' => '{Spouse PpNum}',

      // Correspondence
      'PosAddr' => '1/F., 1st First Street, Kwun Tong.',
      'AreaCodePosAddr' => 'K',
      'PhoneNum' => '98765432',

      // Position
      'Capacity' => 'ACCOUNTANT',

      // Employment Period
      'StartDateOfService' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfService' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income
      // 1. Type 1
      'PerOfType1' => $perOfEmp,
      'AmtOfType1' => toCurrency(1000),
      //
      // 2. Type 2
      'PerOfType2' => $perOfEmp,
      'AmtOfType2' => toCurrency(2000),
      //
      // 4. Type 3
      'PerOfType3' => $perOfEmp,
      'AmtOfType3' => toCurrency(3000),
      //
      // 6. Artist Fee
      'PerOfArtistFee' => $perOfEmp,
      'AmtOfArtistFee' => toCurrency(4000),
      //
      // 7. Copyright
      'PerOfCopyright' => $perOfEmp,
      'AmtOfCopyright' => toCurrency(5000),
      //
      // 8. Consultant Fee
      'PerOfConsultFee' => $perOfEmp,
      'AmtOfConsultFee' => toCurrency(6000),
      //
      // 9.
      'PerOfOtherInc1' => $perOfEmp,
      'AmtOfOtherInc1' => toCurrency(7000),
      'NatureOtherInc1' => 'Service Fees',
      //
      //
      'PerOfOtherInc2' => $perOfEmp,
      'AmtOfOtherInc2' => toCurrency(8000),
      'NatureOtherInc2' => 'Special Bonus',

      // total
      'TotalIncome' => toCurrency(36000),

      'IndOfSumWithheld' => '1',
      'AmtOfSumWithheld' => toCurrency( 10000 ),

      // Remark
      'IndOfRemark' => '1',
      'Remarks' => '{Remarks}'
    ];
  }


}