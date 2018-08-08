<?php namespace App\Helpers\IrData;

use App\Helpers\FormHelper;

class IrBaseTestHelper
{
  public static function getIrdMaster($langCode='en-us')
  {
    $isEnglish = $langCode=='en-us';

    // Fiscal Year Info
    $fiscalYearInfo = FormHelper::getFiscalYearInfo();
    $headerPeriod = $isEnglish ?
      'for the year from 1 April ' . ($fiscalYearInfo['startYear']) . ' to 31 March ' . ($fiscalYearInfo['endYear']) :
      '在 ' . $fiscalYearInfo['startYear'] . ' 年 4 月 1 日至 ' . $fiscalYearInfo['endYear'] . ' 年 3 月 31 日 年內';

    // File No
    $fileNo = '6A1-1234567';
    $section = '6A1';
    $ern = '1234567';

    // Company Info
    $erName = 'ABC Company Ltd.';
    $erAddr = 'Flat 1, 1/F., 1 First Sheet, Kwun Tong';

    // Basic Form Info
    $designation = 'Manager';
    $signatureName = 'John Chan';
    $formDate = date('Y-m-d');

    // Non-ird fields
    $result = [
      'HeaderPeriod' => strtoupper($headerPeriod),
      'EmpPeriod' => $headerPeriod . ':',
      'IncPeriod' => 'Particulars of income accuring ' . $headerPeriod,
      'FileNo' => $fileNo,

      // for Chinese version only
      'HeaderPeriodFromYear' => $fiscalYearInfo['startYear'],
      'HeaderPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
      'EmpPeriodFromYear' => $fiscalYearInfo['startYear'],
      'EmpPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
      'IncPeriodFromYear' => $fiscalYearInfo['startYear'],
      'IncPeriodToYear' => $fiscalYearInfo['startYear'] + 1,

      // Ird fields
      'Section' => $section,
      'ERN' => $ern,
      'AssYr' => $fiscalYearInfo['startYear'] + 1,
      'YrErReturn' => $fiscalYearInfo['startYear'] + 1,
      'SubDate' => phpDateFormat($formDate, 'd/m/Y'),
      'PayerName' => $erName,
      'ErName' => $erName,
      'ErAddr' => $erAddr,
      'Designation' => $designation,
      'SignatureName' => $signatureName,
      'NoRecordBatch' => 1,
      'TotIncomeBatch' => 0, // isset($formSummary) ? $formSummary['totalEmployeeIncome'] : 0,
      'Employees' => [],
      'Recipient' => []
    ];
    return $result;

  }
}