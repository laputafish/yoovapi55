<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56bHelper extends IrDataHelper
{
  protected static $irdCode = 'IR56B';

  public static function prepareResult($sheetNo, $formInfo, $employeeInfo, $maritalInfo, $incomeInfo) {

//    $isSample = array_key_exists('mode', $options) ? $options['mode'] == 'sample' : false;
//    $isTesting = array_key_exists('mode', $options) ? $options['mode'] == 'testing' : false;
//    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
//    $form = array_key_exists('form', $options) ? $options['form'] : null;
//
//    if($isTesting) {
//      $defaults = static::getTestingDefaults();
//    }
//
//    self::$team = $team;
//    $oaAuth = OAHelper::refreshTokenByTeam(self::$team);
//    self::$employeeId = $employeeId;
//    self::$oaAuth = $oaAuth;
//    $oaEmployee = self::getOAAdminEmployee();
//    if (is_null($oaEmployee)) {
//      return null;
//    }
//
//    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
//    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);
//    $formInfo = self::getFormInfo($oaEmployee, $defaults, $fiscalYearInfo);
//    $employeeInfo = self::getEmployeeInfo($oaEmployee, $defaults);
//    $maritalInfo = self::getMaritalInfo($oaEmployee, $defaults);
//    $incomeInfo = self::getIncomeInfo(
//      $oaAuth,
//      $team,
//      $oaEmployee,
//      $fiscalYearInfo,
//      $formInfo['PerOfEmp'],
//      $defaults);

    return [
      // Ird fields
      'SheetNo' => $sheetNo,
      'TypeOfForm' => $formInfo['TypeOfForm'],'O', // Original, Supplementary, Replacement

      'Surname' => $employeeInfo['Surname'],
      'GivenName' => $employeeInfo['GivenName'],
      'NameInChinese' => $employeeInfo['NameInChinese'],
      'NameInEnglish' => $employeeInfo['NameInEnglish'], // for Control List
      'HKID' => $employeeInfo['HKID'],
      'Sex' => $employeeInfo['Sex'],
      'PpNum' => $employeeInfo['PpNum'],

      // Employee's Spouse
      'MaritalStatus' => $maritalInfo['MaritalStatus'],
      'SpouseName' => $maritalInfo['SpouseName'],
      'SpouseHKID' => $maritalInfo['SpouseHKID'],
      'SpousePpNum' => $maritalInfo['SpousePpNum'],

      // Correspondence
      'ResAddr' => $employeeInfo['ResAddr'],
      'AreaCodeResAddr' => $employeeInfo['AreaCodeResAddr'],
      'PosAddr' => $employeeInfo['PosAddr'],
      'AreaCodePosAddr' => $employeeInfo['AreaCodePosAddr'],

      // Position
      'Capacity' => $employeeInfo['Capacity'],
      'PtPrinEmp' => $employeeInfo['PtPrinEmp'],

      'StartDateOfEmp' => phpDateFormat($formInfo['EmpStartDate'], 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($formInfo['EmpEndDate'], 'd/m/Y'),

      // Income Particulars
      // 1. Salary
      'PerOfSalary' => $incomeInfo['PerOfSalary'],
      'AmtOfSalary' => toCurrency($incomeInfo['AmtOfSalary']),
      //
      // 2. LeavePay
      'PerOfLeavePay' => $incomeInfo['PerOfLeavePay'],
      'AmtOfLeavePay' => toCurrency($incomeInfo['AmtOfLeavePay']),
      //
      // 3. DirectorFee
      'PerOfDirectorFee' => $incomeInfo['PerOfDirectorFee'],
      'AmtOfDirectorFee' => toCurrency($incomeInfo['AmtOfDirectorFee']),
      //
      // 4. CommFee
      'PerOfCommFee' => $incomeInfo['PerOfCommFee'],
      'AmtOfCommFee' => toCurrency($incomeInfo['AmtOfCommFee']),
      //
      // 5. Bonus
      'PerOfBonus' => $incomeInfo['PerOfBonus'],
      'AmtOfBonus' => toCurrency($incomeInfo['AmtOfBonus']),
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
      // 9. EduBen
      'PerOfEduBen' => $incomeInfo['PerOfEduBen'],
      'AmtOfEduBen' => toCurrency($incomeInfo['AmtOfEduBen']),
      //
      // 10. GainShareOption
      'PerOfGainShareOption' => $incomeInfo['PerOfGainShareOption'],
      'AmtOfGainShareOption' => toCurrency($incomeInfo['AmtOfGainShareOption']),
      //
      // 11.1
      'NatureOtherRAP1' => $incomeInfo['NatureOtherRAP1'],
      'PerOfOtherRAP1' => $incomeInfo['PerOfOtherRAP1'],
      'AmtOfOtherRAP1' => $incomeInfo['AmtOfOtherRAP1'],
      // 11.2
      'NatureOtherRAP2' => $incomeInfo['NatureOtherRAP2'],
      'PerOfOtherRAP2' => $incomeInfo['PerOfOtherRAP2'],
      'AmtOfOtherRAP2' => $incomeInfo['AmtOfOtherRAP2'],
      // 11.3
      'NatureOtherRAP3' => $incomeInfo['NatureOtherRAP3'],
      'PerOfOtherRAP3' => $incomeInfo['PerOfOtherRAP3'],
      'AmtOfOtherRAP3' => $incomeInfo['AmtOfOtherRAP3'],
      //
      // 12. Pension
      'PerOfPension' => $incomeInfo['PerOfPension'],
      'AmtOfPension' => toCurrency($incomeInfo['AmtOfPension']),

      // total
      'TotalIncome' => toCurrency($incomeInfo['TotalIncome']),

      // Place of Residence
      'PlaceOfResInd' => '0',

      // Place #1
      'AddrOfPlace1' => $incomeInfo['AddrOfPlace1'],
      'NatureOfPlace1' => $incomeInfo['NatureOfPlace1'],
      'PerOfPlace1' => $incomeInfo['PerOfPlace1'],
      'RentPaidEr1' => toCurrency($incomeInfo['RentPaidEr1']),
      'RentPaidEe1' => toCurrency($incomeInfo['RentPaidEe1']),
      'RentRefund1' => toCurrency($incomeInfo['RentRefund1']),
      'RentPaidErByEe1' => toCurrency($incomeInfo['RentPaidErByEe1']),

      // Place #2
      'AddrOfPlace2' => $incomeInfo['AddrOfPlace2'],
      'NatureOfPlace2' => $incomeInfo['NatureOfPlace2'],
      'PerOfPlace2' => $incomeInfo['PerOfPlace2'],
      'RentPaidEr2' => toCurrency($incomeInfo['RentPaidEr2']),
      'RentPaidEe2' => toCurrency($incomeInfo['RentPaidEe2']),
      'RentRefund2' => toCurrency($incomeInfo['RentRefund2']),
      'RentPaidErByEe2' => toCurrency($incomeInfo['RentPaidErByEe2']),

      // Non-Hong Kong Income
      'OverseaIncInd' => $incomeInfo['OverseaIncInd'],
      'AmtPaidOverseaCo' => $incomeInfo['AmtPaidOverseaCo'],
      'NameOfOverseaCo' => $incomeInfo['NameOfOverseaCo'],
      'AddrOfOverseaCo' => $incomeInfo['AddrOfOverseaCo'],

      // Remark
      'Remarks' => $formInfo['Remarks']
    ];
  }

  public static function getxxx($team, $employeeId, $options = [])
  {
    $isSample = array_key_exists('mode', $options) ? $options['mode'] == 'sample' : false;
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    // $formSummary = array_key_exists('formSummary', $options) ? $options['formSummary'] : null;
    $form = array_key_exists('form', $options) ? $options['form'] : null;

    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);
    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    $oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    //***
    // form->fiscal_year is the year of fiscal year end date
    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;

    // Grab data from OA
    $oaEmployee = self::getOAAdminEmployee();
    $oaPayrollSummary = self::getOAPayrollSummary(
      $oaAuth,
      $team,
      $oaEmployee,
      $fiscalYearInfo);

    if (is_null($oaEmployee)) {
      return null;
    }

    $joinedDate = substr($oaEmployee['joinedDate'], 0, 10);
    $jobEndedDate = substr($oaEmployee['jobEndedDate'], 0, 10);

    $empStartDate = $fiscalYearInfo['startDate'] > $joinedDate ? $fiscalYearInfo['startDate'] : $joinedDate;
    $empEndDate = isset($oaEmployee['jobEndedDate']) ?
      ($fiscalYearInfo['endDate'] < $jobEndedDate ? $fiscalYearInfo['endDate'] : $jobEndedDate) :
      $fiscalYearInfo['endDate'];

    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    // Employee
    if (isset($oaEmployee['jobEndedDate'])) {
      $jobEndedDate = phpDateFormat($oaEmployee['jobEndedDate'], 'd/m/Y');
      $fiscalYearStartBeforeCease = phpDateFormat(getFiscalYearStartOfDate($jobEndedDate), 'd/m/Y');
    } else {
      $jobEndedDate = '';
      $fiscalYearStartBeforeCease = '';
    }
    // 1=Single/Widowed/Divorced/Living Apart, 2=Married
    $maritalStatus = array_key_exists('maritalStatus', $defaults) ?
      $defaults['maritalStatus'] :
      ($oaEmployee['marital'] == 'married' ? 2 : 1);

    $result = [
      // Ird fields
      'SheetNo' => $sheetNo,
      'HKID' => $oaEmployee['identityNumber'],
      'TypeOfForm' => 'O', // Original, Supplementary, Replacement
      'Surname' => $oaEmployee['lastName'],
      'GivenName' => $oaEmployee['firstName'],
      'NameInChinese' => getOAEmployeeChineseName($oaEmployee),
      'Sex' => $oaEmployee['gender'],
      'MaritalStatus' => $maritalStatus,
      'PpNum' => empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : '',

      // Employee's Spouse
      'SpouseName' => $maritalStatus == 1 ? '' : $defaults['spouseName'],
      'SpouseHKID' => $maritalStatus == 1 ? '' : $defaults['spouseHkid'],
      'SpousePpNum' => $maritalStatus == 1 ? '' : $defaults['spousePpNum'],

      // Correspondence
      'ResAddr' => $oaEmployee['address'][0]['text'],
      'AreaCodeResAddr' => '', // array_key_exists('areaCodeResAddr', $defaults) ? $defaults['areaCodeResAddr'],
      'PosAddr' => count($oaEmployee['address']) > 1 ? $oaEmployee['address'][1] : trans('tax.same_as_above'),

      // Position
      'Capacity' => strtoupper($oaEmployee['jobTitle']),
      'PtPrinEmp' => array_key_exists('ptPrinEmp', $defaults) ?
        $defaults['ptPrinEmp'] :
        '',

      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income Particulars
      // 1. Salary,
      // 2. LeavePay,
      // 3. DirectorFee,
      // 4. CommFee,
      // 5. Bonus,
      // 6. BpEtc,
      // 7. PayRetire,
      // 8. SalTaxPaid,
      // 9. EduBen,
      // 10. GainShareOption,
      // 11.1
      'NatureOtherRAP1' => '',
      'PerOfOtherRAP1' => '',
      'AmtOfOtherRAP1' => '',
      // 11.2
      'NatureOtherRAP2' => '',
      'PerOfOtherRAP2' => '',
      'AmtOfOtherRAP2' => '',
      // 11.3
      'NatureOtherRAP3' => '',
      'PerOfOtherRAP3' => '',
      'AmtOfOtherRAP3' => '',
      // 12. Pension

      // total
      'TotalIncome' => toCurrency($oaPayrollSummary['totalIncome']),

      // Place of Residence
      'PlaceOfResInd' => '0',

      // Place #1
      'AddrOfPlace1' => '',
      'NatureOfPlace1' => '',
      'PerOfPlace1' => '',
      'RentPaidEr1' => '',
      'RentPaidEe1' => '',
      'RentRefund1' => '',
      'RentPaidErByEe1' => '',

      // Place #2
      'AddrOfPlace2' => '',
      'NatureOfPlace2' => '',
      'PerOfPlace2' => '',
      'RentPaidEr2' => '',
      'RentPaidEe2' => '',
      'RentRefund2' => '',
      'RentPaidErByEe2' => '',

      // Non-Hong Kong Income
      'OverseaIncInd' => '0',
      'AmtPaidOverseaCo' => '',
      'NameOfOverseaCo' => '',
      'AddrOfOverseaCo' => '',

      // Remark
      'Remarks' => array_key_exists('remarks', $defaults) ? $defaults['remarks'] : ''
    ];

    // Income Particulars
    $tableMapping = [
      'Salary' => 'salary',
      'LeavePay' => 'leave_pay',
      'DirectorFee' => 'director_fee',
      'CommFee' => 'comm_fee',
      'Bonus' => 'bonus',
      'BpEtc' => 'bp_etc',
      'PayRetire' => 'pay_retire',
      'SalTaxPaid' => 'sal_tax_paid',
      'EduBen' => 'edu_ben',
      'GainShareOption' => 'gain_share_option',
      'Pension' => 'pension'
    ];
    foreach ($tableMapping as $irdField => $token) {
      if ($irdField == 'Salary') {
        $result['PerOf' . $irdField] = $perOfEmp;
      } else {
        $result['PerOf' . $irdField] = $oaPayrollSummary[$token] > 0 ? $perOfEmp : '';
      }

      $result['AmtOf' . $irdField] = toCurrency($oaPayrollSummary[$token]);
    }

    if (count($oaPayrollSummary['other_raps']) > 0) {
      $result['NatureOtherRAP1'] = $oaPayrollSummary['other_raps'][0]['nature'];
      $result['PerOfOtherRAP1'] = $perOfEmp;
      $result['AmtOfOtherRAP1'] = $oaPayrollSummary['other_raps'][0]['amt'];
    }

    if (count($oaPayrollSummary['other_raps']) > 1) {
      $result['NatureOtherRAP2'] = $oaPayrollSummary['other_raps'][1]['nature'];
      $result['PerOfOtherRAP2'] = $perOfEmp;
      $result['AmtOfOtherRAP2'] = $oaPayrollSummary['other_raps'][1]['amt'];
    }

    if (count($oaPayrollSummary['other_raps']) > 2) {
      $result['NatureOtherRAP3'] = $oaPayrollSummary['other_raps'][2]['nature'];
      $result['PerOfOtherRAP3'] = $perOfEmp;
      $result['AmtOfOtherRAP3'] = $oaPayrollSummary['other_raps'][2]['amt'];
    }

    if (array_key_exists('placeOfResInd', $defaults)) {
      $result['PlaceOfResInd'] = $defaults['placeOfResInd'];
      if ($defaults['placeOfResInd'] == '1') {
        $result['AddrOfPlace1'] = $defaults['addrOfPlace1'];
        $result['NatureOfPlace1'] = $defaults['natureOfPlace1'];
        $result['PerOfPlace1'] = $defaults['perOfPlace1'];
        $result['RentPaidEr1'] = toCurrency($defaults['rentPaidEr1']);
        $result['RentPaidEe1'] = toCurrency($defaults['rentPaidEe1']);
        $result['RentRefund1'] = toCurrency($defaults['rentRefund1']);
        $result['RentPaidErByEe1'] = toCurrency($defaults['rentPaidErByEe1']);
        // Place #2
        $result['AddrOfPlace2'] = $defaults['addrOfPlace2'];
        $result['NatureOfPlace2'] = $defaults['natureOfPlace2'];
        $result['PerOfPlace2'] = $defaults['perOfPlace2'];
        $result['RentPaidEr2'] = toCurrency($defaults['rentPaidEr2']);
        $result['RentPaidEe2'] = toCurrency($defaults['rentPaidEe2']);
        $result['RentRefund2'] = toCurrency($defaults['rentRefund2']);
        $result['RentPaidErByEe2'] = toCurrency($defaults['rentPaidErByEe2']);
      }
    }

    if (array_key_exists('overseaIncInd', $defaults)) {
      $result['OverseaIncInd'] = $defaults['overseaIncInd'];
      if ($defaults['overseaIncInd'] == '1') {
        $result['AmtPaidOverseaCo'] = $defaults['amtPaidOverseaCo'];
        $result['NameOfOverseaCo'] = $defaults['nameOfOverseaCo'];
        $result['AddrOfOverseaCo'] = $defaults['addrOfOverseaCo'];
      }
    }

    if (array_key_exists('remarks', $defaults)) {
      $result['Remarks'] = $defaults['remarks'];
    }

    return $result;
  }
}
