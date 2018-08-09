<?php namespace App\Helpers\IrData;

class Ir56bTestHelper extends IrBaseTestHelper {
  public static function get($langCode) {
    $isEnglish = $langCode == 'en-us';
    $empStartDate = '2017-04-01';
    $empEndDate = '2018-03-31';
    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    return [
      // Employee's Info
      'SheetNo' => 1,
      'TypeofForm' => 'O',

      'Surname' => '{Surname}',
      'GivenName' => '{GivenName}',
      'NameInEnglish' => 'Chan, Tai Man',
      'NameInChinese' => '陳大文',
      'HKID' => 'C1234567',
      'Sex' => 'M',
      'PpNum' => '{PpNum}',

      // Employee's marital status
      'MaritalStatus' => '1',
      'SpouseName' => '{Spouse Name}',
      'SpouseHKID' => '{Spouse HKID}',
      'SpousePpNum' => '{Spouse PpNum}',

      // Correspondence
      'ResAddr' => '1/F., 1st First Street, Kwun Tong.',
      'AreaCodeResAddr' => 'K',
      'PosAddr' => '{Same as Above}',
      'AreaCodePosAddr' => 'K',

      // Position
      'Capacity' => 'ACCOUNTANT',
      'PtPrinEmp' => 'DEF Company Ltd.',

      // Employment Period
      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income
      // 1. Salary
      'PerOfSalary' => $perOfEmp,
      'AmtOfSalary' => toCurrency(1000),
      //
      // 2. Leave Pay
      'PerOfLeavePay' => $perOfEmp,
      'AmtOfLeavePay' => toCurrency(2000),
      //
      // 3. Director Fee
      'PerOfDirectorFee' => $perOfEmp,
      'AmtOfDirectorFee' => toCurrency(3000),
      //
      // 4. Comm Fee
      'PerOfCommFee' => $perOfEmp,
      'AmtOfCommFee' => toCurrency(4000),
      //
      // 5. Bonus
      'PerOfBonus' => $perOfEmp,
      'AmtOfBonus' => toCurrency(5000),
      //
      // 6. BpEtc
      'PerOfBpEtc' => $perOfEmp,
      'AmtOfBpEtc' => toCurrency(6000),
      //
      // 7. PayRetire
      'PerOfPayRetire' => $perOfEmp,
      'AmtOfPayRetire' => toCurrency(7000),
      //
      // 8. SalTaxPaid
      'PerOfSalTaxPaid' => $perOfEmp,
      'AmtOfSalTaxPaid' => toCurrency(8000),
      //
      // 9. EduBen
      'PerOfEduBen' => $perOfEmp,
      'AmtOfEduBen' => toCurrency(9000),
      //
      // 10. GainShareOption
      'PerOfGainShareOption' => $perOfEmp,
      'AmtOfGainShareOption' => toCurrency(10000),
      // 11.1
      'PerOfOtherRAP1' => $perOfEmp,
      'AmtOfOtherRAP1' => toCurrency(11000),
      'NatureOtherRAP1' => '{Nature of other RAP1}',
      // 11.2
      'PerOfOtherRAP2' => $perOfEmp,
      'AmtOfOtherRAP2' => toCurrency(12000),
      'NatureOtherRAP2' => '{Nature of other RAP2}',
      // 11.3
      'PerOfOtherRAP3' => $perOfEmp,
      'AmtOfOtherRAP3' => toCurrency(13000),
      'NatureOtherRAP3' => '{Nature of other RAP3}',
      //
      // 12. Pension
      'PerOfPension' => $perOfEmp,
      'AmtOfPension' => toCurrency(14000),

      // total
      'TotalIncome' => toCurrency(105000),

      // Place of Residence
      'PlaceOfResInd' => '1',

      // Place #1
      'AddrOfPlace1' => '{AddrOfPlace1}',
      'NatureOfPlace1' => '{NatureOfPlace1}',
      'PerOfPlace1' => $perOfEmp,
      'RentPaidEr1' => toCurrency(1000),
      'RentPaidEe1' => toCurrency(2000),
      'RentRefund1' => toCurrency(3000),
      'RentPaidErByEe1' => toCurrency(4000),

      // Place #2
      'AddrOfPlace2' => '{AddrOfPlace2}',
      'NatureOfPlace2' => '{NatureOfPlace2}',
      'PerOfPlace2' => $perOfEmp,
      'RentPaidEr2' => toCurrency(2000),
      'RentPaidEe2' => toCurrency(3000),
      'RentRefund2' => toCurrency(4000),
      'RentPaidErByEe2' => toCurrency(5000),

      // Non-Hong Kong Income
      'OverseaIncInd' => '1',
      'AmtPaidOverseaCo' => toCurrency(10000),
      'NameOfOverseaCo' => '{Name of oversea company}',
      'AddrOfOverseaCo' => '{Address of oversea company}',

      // Remark
      'Remarks' => '{Remarks}'

    ];
  }


}