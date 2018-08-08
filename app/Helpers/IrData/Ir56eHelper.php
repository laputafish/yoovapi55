<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56eHelper extends IrDataHelper {

  protected static $irdCode = 'IR56E';

  public static function get($team, $employeeId, $options=[]) {
    $isSample = array_key_exists('mode', $options) ? $options['mode'] == 'sample' : false;
    $isTesting = array_key_exists('mode', $options) ? $options['mode'] == 'testing' : false;
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    $form = array_key_exists('form', $options) ? $options['form'] : null;

    if($isTesting) {
      $defaults = self::getTestingDefaults();
    }

    self::$team = $team;
    $oaAuth = OAHelper::refreshTokenByTeam(self::$team);
    self::$employeeId = $employeeId;
    self::$oaAuth = $oaAuth;
    $oaEmployee = self::getOAAdminEmployee();
    if (is_null($oaEmployee)) {
      return null;
    }

    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);
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
      // Employee Info
      'NameInEnglish' => $employeeInfo['NameInEnglish'],
      'NameInChinese' => $employeeInfo['NameInChinese'],
      'HKID' => $employeeInfo['HKID'],
      'PpNum' => $employeeInfo['PpNum'],
      'Sex' => $employeeInfo['Sex'],

      // Employee's marital info
      // 1=Single/Widowed/Divorced/Living Apart, 2=Married
      'MaritalStatus' => $maritalInfo['MaritalStatus'],
      'SpouseName' => $maritalInfo['SpouseName'],
      'SpouseHkid' => $maritalInfo['SpouseHKID'],
      'SpousePpNum' => $maritalInfo['SpousePpNum'],
  
      // Correspondence
      'ResAddr' => $employeeInfo['ResAddr'],
      'PosAddr' => $employeeInfo['PosAddr'],

      // Position
      'Capacity' => $employeeInfo['Capacity'],
      'StartDateOfEmp' => phpDateFormat($oaEmployee['joinedDate'], 'd/m/Y'),

      'MonthlyFixedIncome' => toCurrency($incomeInfo['MonthlyFixedIncome']),
      'MonthlyAllowance' => toCurrency($incomeInfo['MonthlyAllowance']),
      'FluctuatingIncome' => toCurrency($incomeInfo['FluctuatingIncome']),
  
      // Place of residence
      'PlaceProvided' => $incomeInfo['PlaceProvided'],
      'AddrOfPlace' => $incomeInfo['AddrOfPlace'],
      'NatureOfPlace' => $incomeInfo['NatureOfPlace'],
      'RentPaidEr' => $incomeInfo['RentPaidEr'],
      'RentPaidEe' => $incomeInfo['RentPaidEe'],
      'RentRefund' => $incomeInfo['RentRefund'],
      'RentPaidErByEe' => $incomeInfo['RentPaidErByEe'],
  
      // Non-Hong Kong Income
      'OverseaIncInd' => $incomeInfo['OverseaIncInd'],
      'AmtPaidOverseaCo' => $incomeInfo['AmtPaidOverseaCo'],
      'NameOfOverseaCo' => $incomeInfo['NameOfOverseaCo'],
      'AddrOfOverseaCo' => $incomeInfo['AddrOfOverseaCo'],
  
      // share option
      'ShareBeforeEmp' => $incomeInfo['ShareBeforeEmp']
    ];
    return $result;
  }
}