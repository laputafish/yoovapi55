<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;
use App\Helpers\FormHelper;

class Ir56bHelper extends IrDataHelper
{

  public static function get($team, $employeeId, $options = [])
  {
    $isSample = array_key_exists('mode', $options) ? $options['mode']=='sample' : false;
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    // $formSummary = array_key_exists('formSummary', $options) ? $options['formSummary'] : null;
    $form = array_key_exists('form', $options) ? $options['form'] : null;

    $fiscalYearInfo = FormHelper::getFiscalYearInfo($form);
    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    //***
    // form->fiscal_year is the year of fiscal year end date
    $sheetNo = array_key_exists('sheetNo', $options) ? $options['sheetNo'] : 1;
//    if (isset($form)) {
//      $signatureName = $form->signature_name;
//      $designation = $form->designation;
//      $formDate = $form->form_date;
////      $fiscalYearStart = ($form->fiscal_start_year - 1) . '-04-01';
//      if (array_key_exists('sheetNo', $options)) {
//        $sheetNo = $options['sheetNo'];
//      }
//    } else {
//      $signatureName = $team->getSetting('default_signature_name', '(No signature name)');
//      $designation = $team->getSetting('designation', '(No designation)');
//      $formDate = date('Y-m-d');
////      if(array_key_exists('year', $options)) {
////        $year = $options['year'];
////        $fiscalYearStart = ($year-1).'-04-01';
////      } else {
////        $fiscalYearStart = getCurrentFiscalYearStartDate();
////      }
//    }
////    $fiscalStartYear = (int)substr($fiscalYearStart, 0, 4);
////    $fiscalYearPeriod = [
////      'startDate' => $fiscalStartYear . '-04-01',
////      'endDate' => ($fiscalStartYear + 1) . '-03-31'
////    ];

    // Grab data from OA
//    $oaTeam = self::getOATeam();
    $oaEmployee = self::getOAAdminEmployee();
// $oaSalaries = self::getOASalary();
    $oaPayrollSummary = self::getOAPayrollSummary($fiscalYearInfo);

    if (is_null($oaEmployee)) {
      return null;
    }

    $joinedDate = substr($oaEmployee['joinedDate'], 0, 10);
    $jobEndedDate = substr($oaEmployee['jobEndedDate'], 0, 10);

    $empStartDate = $fiscalYearInfo['startDate'] > $joinedDate ? $fiscalYearInfo['startDate'] : $joinedDate;
    $empEndDate = isset($oaEmployee['jobEndedDate']) ?
      ($fiscalYearInfo['endDate'] < $jobEndedDate ? $fiscalYearPeriod['endDate'] : $jobEndedDate) :
      $fiscalYearInfo['endDate'];

    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

//    $irdMaster = array_key_exists('irdMaster', $options) ? $options['irdMaster'] : [];

//    // Company
//    $registrationNumber = $oaTeam['setting']['registrationNumber'];
//    $registrationNumberSegs = explode('-', $registrationNumber);
//    $section = $registrationNumberSegs[0];
//    $ern = $registrationNumberSegs[1];
//    $headerPeriod = 'for the year from 1 April ' . ($fiscalYearInfo['startYear']) . ' to 31 March ' . ($fiscalYearInfo['endYear'] + 1);

//    $result = array_key_exists('irdMaster', $options) ? $options['irdMaster'] : [
//      // Non-ird fields
//      'HeaderPeriod' => strtoupper($headerPeriod),
//      'EmpPeriod' => $headerPeriod . ':',
//      'IncPeriod' => 'Particulars of income accuring '.$headerPeriod,
//      'FileNo' => $registrationNumber,
//
//      // for Chinese version only
//      'HeaderPeriodFromYear' => $fiscalYearInfo['startYear'],
//      'HeaderPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
//      'EmpPeriodFromYear' => $fiscalYearInfo['startYear'],
//      'EmpPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
//      'IncPeriodFromYear' => $fiscalYearInfo['startYear'],
//      'IncPeriodToYear' => $fiscalYearInfo['startYear'] + 1,
//
//      // Ird fields
//      'Section' => $section,
//      'ERN' => $ern,
//      'YrErReturn' => $fiscalYearInfo['startYear'] + 1,
//      'SubDate' => phpDateFormat($formDate, 'd/m/Y'),
//      'ErName' => $oaTeam['name'],
//      'Designation' => $designation,
//      'NoRecordBatch' => isset($form) ? $form->employees->count() : 1,
//      'TotIncomeBatch' => isset($formSummary) ? $formSummary['totalEmployeeIncome'] : 0,
//    ];

    //*************************************************************************************




    // Employee
    if (isset($oaEmployee['jobEndedDate'])) {
      $jobEndedDate = phpDateFormat($oaEmployee['jobEndedDate'], 'd/m/Y');
      $fiscalYearStartBeforeCease = phpDateFormat(getFiscalYearStartOfDate($jobEndedDate), 'd/m/Y');
    } else {
      $jobEndedDate = '';
      $fiscalYearStartBeforeCease = '';
    }
    // 1=Single/Widowed/Divorced/Living Apart, 2=Married
    $martialStatus = array_key_exists('martialStatus', $defaults) ?
      $defaults['martialStatus'] :
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
      'MartialStatus' => $martialStatus,
      'PpNum' => empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : '',

      // Employee's Spouse
      'SpouseName' => $martialStatus == 1 ? '' : $defaults['spouseName'],
      'SpouseHKID' => $martialStatus == 1 ? '' : $defaults['spouseHkid'],
      'SpousePpNum' => $martialStatus == 1 ? '' : $defaults['spousePpNum'],

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
      'TotalIncome' => toCurrency( $oaPayrollSummary['totalIncome'] ),
      
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
    foreach($tableMapping as $irdField=>$token) {
      $result['PerOf'.$irdField] = $oaPayrollSummary[$token] > 0 ? $perOfEmp : '';
      $result['AmtOf'.$irdField] = toCurrency($oaPayrollSummary[$token]);
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

    if(array_key_exists('placeOfResInd', $defaults)) {
      $result['PlaceOfResInd'] = $defaults['placeOfResInd'];
      if($defaults['placeOfResInd'] == '1') {
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

    if(array_key_exists('overseaIncInd', $defaults)) {
      $result['OverseaIncInd'] = $defaults['overseaIncInd'];
      if ($defaults['overseaIncInd'] == '1') {
        $result['AmtPaidOverseaCo'] = $defaults['amtPaidOverseaCo'];
        $result['NameOfOverseaCo'] = $defaults['nameOfOverseaCo'];
        $result['AddrOfOverseaCo'] = $defaults['addrOfOverseaCo'];
      }
    }

    if(array_key_exists('remarks', $defaults)) {
      $result['Remarks'] = $defaults['remarks'];
    }

    return $result;
  }
}