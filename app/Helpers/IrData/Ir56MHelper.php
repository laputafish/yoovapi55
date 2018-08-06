<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56MHelper extends IrDataHelper
{

  public static function get($team, $employeeId, $options = [])
  {
    $isSample = array_key_exists('mode', $options) ? $options['mode']=='sample' : false;
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    // $formSummary = array_key_exists('formSummary', $options) ? $options['formSummary'] : null;
    $form = array_key_exists('form', $options) ? $options['form'] : null;

    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);

    self::$team = $team;
    $oaAuth = $oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    self::$employeeId = $employeeId;
    self::$oaAuth = $oaAuth;

    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
    $oaEmployee = self::getOAAdminEmployee();
    if (is_null($oaEmployee)) {
      return null;
    }

    $formInfo = self::getFormInfo($oaEmployee, $defaults, $fiscalYearInfo);
    $employeeInfo = self::getEmployeeInfo($oaEmployee, $defaults);
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
      'ComRecNameEng' => '',
      'ComRecNameChi' => '',
      'ComRecBRN' => '',

      // Employee Info
      'HKID' => $employeeInfo['HKID'],
      'NameInEnglish' => $employeeInfo['NameInEnglish'],
      'NameInChinese' => $employeeInfo['NameInChinese'],
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

      'AmtOfType1' => toCurrency($incomeInfo['AmtOfType1']),
      'AmtOfType2' => toCurrency($incomeInfo['AmtOfType2']),
      'AmtOfType3' => toCurrency($incomeInfo['AmtOfType3']),
      'AmtOfArtistFee' => toCurrency($incomeInfo['AmtOfArtistFee']),
      'AmtOfCopyright' => toCurrency($incomeInfo['AmtOfCopyright']),
      'AmtOfConsultFee' => toCurrency($incomeInfo['AmtOfConsultFee']),
      'NatureOtherInc1' => $incomeInfo['NatureOtherInc1'],
      'AmtOfOtherInc1' => toCurrency($incomeInfo['AmtOfOtherInc1']),
      'NatureOtherInc2' => $incomeInfo['NatureOtherInc2'],
      'AmtOfOtherInc2' => toCurrency($incomeInfo['AmtOfOtherInc2']),
      'TotalIncome' => toCurrency($incomeInfo['TotalIncome']),
      'IndOfSumWithheld' => $incomeInfo['IndOfSumWithheld'],
      'AmtOfSumWithheld' => toCurrency($incomeInfo['AmtOfSumWithheld']),
      'IndOfRemark' => $incomeInfo['IndOfRemark'],
      'Remarks' => $formInfo['Remarks']
    ];

    return $result;
  }
}
