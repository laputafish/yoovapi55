<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56mHelper extends IrDataHelper
{
  protected static $irdCode = 'IR56M';

  protected static function getTestingDefaults() {
    return [
      'ComRecNameEng' => 'ComRecNameEng',
      'ComRecNameChi' => 'ComRecNameChi',
      'ComRecBRN' => 'ComRecBRN',
      'SpouseName' => '{Spouse Name}',
      'SpouseHKID' => '{Spouse HKID}',
      'SpousePpNum' => '{Spouse Pp Num}',

      'AmtOfType1' => 100,
      'AmtOfType2' => 200,
      'AmtOfType3' => 300,
      'AmtOfArtistFee' => 400,
      'AmtOfCopyright' => 500,
      'AmtOfConsultFee' => 600,
      'NatureOtherInc1' => 'Services Fee',
      'AmtOfOtherInc1' => 700,
      'NatureOtherInc2' => 'Maintenance Fee',
      'AmtOfOtherInc2' => 800,

      'IndOfSumWithheld' => '1',
      'AmtOfSumWithheld' => 100000,
      'IndOfRemark' => '1',
      'Remarks' => 'This is remarks'
    ];
  }

  public static function get($team, $employeeId, $options = [])
  {
    $isSample = array_key_exists('mode', $options) ? $options['mode']=='sample' : false;
    $isTesting = array_key_exists('mode', $options) ? $options['mode']=='testing' : false;
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    $form = array_key_exists('form', $options) ? $options['form'] : null;
    $isEnglish = isset($form) ? $form->lang->code=='en-us' : true;

    if($isTesting || static::$forceDefaults) {
      $defaults = static::getTestingDefaults();
    }

    self::$team = $team;
    $oaAuth = $oaAuth = OAHelper::refreshTokenByTeam(self::$team);
    self::$employeeId = $employeeId;
    self::$oaAuth = $oaAuth;
    $oaEmployee = self::getOAAdminEmployee();
    if (is_null($oaEmployee)) {
      return null;
    }

    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);
    $formInfo = self::getFormInfo($oaEmployee, $defaults, $fiscalYearInfo);
    $employeeInfo = self::getEmployeeInfo($oaEmployee, $defaults, $isEnglish); // isEnglish is for address parsing
    $maritalInfo = self::getMaritalInfo($oaEmployee, $defaults);
    $incomeInfo = self::getIncomeInfo(
      $oaAuth,
      $team,
      $oaEmployee,
      $fiscalYearInfo,
      $formInfo['PerOfEmp'],
      $defaults);

    $result = [
      // Ird fields
      'SheetNo' => $sheetNo,
      'ComRecNameEng' => $employeeInfo['ComRecNameEng'],
      'ComRecNameChi' => $employeeInfo['ComRecNameChi'],
      'ComRecBRN' => $employeeInfo['ComRecBRN'],

      // Employee Info
      'NameInEnglish' => $employeeInfo['NameInEnglish'],
      'NameInChinese' => $employeeInfo['NameInChinese'],
      'HKID' => $employeeInfo['HKID'],
      'Sex' => $employeeInfo['Sex'],

      // Employee's Spouse
      'MaritalStatus' => $maritalInfo['MaritalStatus'],
      'SpouseName' => $maritalInfo['SpouseName'],
      'SpouseHKID' => $maritalInfo['SpouseHKID'],
      'SpousePpNum' => $maritalInfo['SpousePpNum'],

      // Correspondence
      'PosAddr' => $employeeInfo['ResAddr'],
      'AreaCodePosAddr' => $employeeInfo['AreaCodeResAddr'],
      'PhoneNum' => $employeeInfo['PhoneNum'],

      // Position
      'Capacity' => $employeeInfo['Capacity'],

      // Employment Period
      'StartDateOfService' => phpDateFormat($formInfo['EmpStartDate'], 'd/m/Y'),
      'EndDateOfService' => phpDateFormat($formInfo['EmpEndDate'], 'd/m/Y'),

      'PerOfType1' => $incomeInfo['PerOfType1'],
      'AmtOfType1' => toCurrency($incomeInfo['AmtOfType1']),

      'PerOfType2' => $incomeInfo['PerOfType2'],
      'AmtOfType2' => toCurrency($incomeInfo['AmtOfType2']),

      'PerOfType3' => $incomeInfo['PerOfType3'],
      'AmtOfType3' => toCurrency($incomeInfo['AmtOfType3']),

      'PerOfArtistFee' => $incomeInfo['PerOfArtistFee'],
      'AmtOfArtistFee' => toCurrency($incomeInfo['AmtOfArtistFee']),

      'PerOfCopyright' => $incomeInfo['PerOfCopyright'],
      'AmtOfCopyright' => toCurrency($incomeInfo['AmtOfCopyright']),

      'PerOfConsultFee' => $incomeInfo['PerOfConsultFee'],
      'AmtOfConsultFee' => toCurrency($incomeInfo['AmtOfConsultFee']),

      'PerOfOtherInc1' => $incomeInfo['PerOfOtherInc1'],
      'AmtOfOtherInc1' => toCurrency($incomeInfo['AmtOfOtherInc1']),
      'NatureOtherInc1' => $incomeInfo['NatureOtherInc1'], // Service Fees

      'PerOfOtherInc2' => $incomeInfo['PerOfOtherInc2'],
      'AmtOfOtherInc2' => toCurrency($incomeInfo['AmtOfOtherInc2']),
      'NatureOtherInc2' => $incomeInfo['NatureOtherInc2'],

      // Total
      'TotalIncome' => toCurrency($incomeInfo['TotalIncome']),

      'IndOfSumWithheld' => $incomeInfo['IndOfSumWithheld'],
      'AmtOfSumWithheld' => toCurrency($incomeInfo['AmtOfSumWithheld']),

      // Remark
      'IndOfRemark' => $formInfo['IndOfRemark'],
      'Remarks' => $formInfo['Remarks']
    ];

    return $result;
  }
}
