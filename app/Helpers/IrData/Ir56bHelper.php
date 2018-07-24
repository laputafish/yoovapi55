<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56bHelper extends IrDataHelper
{

  public static function get($team, $employeeId, $form = null, $options = [])
  {
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];
    $formSummary = array_key_exists('formSummary', $options) ? $options['formSummary'] : null;

    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    //***
    // form->fiscal_year is the year of fiscal year end date
    $sheetNo = 1;
    if (isset($form)) {
      $signatureName = $form->signature_name;
      $designation = $form->designation;
      $formDate = $form->form_date;
      $fiscalYearStart = ($form->fiscal_year - 1) . '-04-01';
      if (array_key_exists('sheetNo', $options)) {
        $sheetNo = $options['sheetNo'];
      }
    } else {
      $signatureName = $team->getSetting('default_signature_name', '(No signature name)');
      $designation = $team->getSetting('designation', '(No designation)');
      $formDate = date('Y-m-d');
      if(array_key_exists('year', $options)) {
        $year = $options['year'];
        $fiscalYearStart = ($year-1).'-04-01';
      } else {
        $fiscalYearStart = getCurrentFiscalYearStartDate();
      }
    }
    $fiscalYear = (int)substr($fiscalYearStart, 0, 4);

    $fiscalYearPeriod = [
      'startDate' => $fiscalYear . '-04-01',
      'endDate' => ($fiscalYear + 1) . '-03-31'
    ];

    // Grab data from OA
    $oaTeam = self::getOATeam();
    $oaEmployee = self::getOAAdminEmployee();
    $oaSalaries = self::getOASalary();
    $oaPayrollSummary = self::getOAPayrollSummary($fiscalYearPeriod);

    if (is_null($oaEmployee)) {
      return null;
    }

    $joinedDate = substr($oaEmployee['joinedDate'], 0, 10);
    $jobEndedDate = substr($oaEmployee['jobEndedDate'], 0, 10);

    $empStartDate = $fiscalYearPeriod['startDate'] > $joinedDate ? $fiscalYearPeriod['startDate'] : $joinedDate;
    $empEndDate = isset($oaEmployee['jobEndedDate']) ?
      ($fiscalYearPeriod['endDate'] < $jobEndedDate ? $fiscalYearPeriod['endDate'] : $jobEndedDate) :
      $fiscalYearPeriod['endDate'];
    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    // Company
    $registrationNumber = $oaTeam['setting']['registrationNumber'];
    $registrationNumberSegs = explode('-', $registrationNumber);
    $section = $registrationNumberSegs[0];
    $ern = $registrationNumberSegs[1];

    $headerPeriod = 'for the year from 1 April ' . ($fiscalYear - 1) . ' to 31 March ' . ($fiscalYear);
    $result = [
      // Non-ird fields
      'HeaderPeriod' => strtoupper($headerPeriod),
      'EmpPeriod' => $headerPeriod . ':',
      'IncPeriod' => 'Particulars of income accuring '.$headerPeriod,
      'FileNo' => $registrationNumber,

      // Ird fields
      'Section' => $section,
      'ERN' => $ern,
      'YrErReturn' => $fiscalYear,
      'SubDate' => phpDateFormat($formDate, 'd/m/Y'),
      'ErName' => $oaTeam['name'],
      'Designation' => $designation,
      'NoRecordBatch' => isset($form) ? $form->employees->count() : 1,
      'TotIncomeBatch' => isset($formSummary) ? $formSummary['totalEmployeeIncome'] : 0,
    ];

    // Employee
    if (isset($oaEmployee['jobEndedDate'])) {
      $jobEndedDate = phpDateFormat($oaEmployee['jobEndedDate'], 'd/m/Y');
      $fiscalYearStartBeforeCease = phpDateFormat(getFiscalYearStartOfDate($jobEndedDate), 'd/m/Y');
    } else {
      $jobEndedDate = '';
      $fiscalYearStartBeforeCease = '';
    }

    // 1=Single/Widowed/Divorced/Living Apart, 2=Married
    $martialStatus = ($oaEmployee['marital'] == 'married' ? 2 : 1);

    $result = array_merge($result, [
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
      'PtPrinEmp' => '', // $defaults['ptPrinEmp'],

      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income Particulars
//      // 1.
//      'PerOfSalary' => $oaPayrollSummary['salary'] > 0 ? $perOfEmp : '', // 20170401-20180331
//      'AmtOfSalary' => toCurrency($oaPayrollSummary['salary']),
//      // 2.
//      'PerOfLeavePay' => $oaPayrollSummary['leave_pay'] > 0 ? $perOfEmp : '',
//      'AmtOfLeavePay' => toCurrency($oaPayrollSummary['leave_pay']),
//      // 3.
//      'PerOfDirectorFee' => $oaPayrollSummary['director_fee'] > 0 ? $perOfEmp : '',
//      'AmtOfDirectorFee' => toCurrency($oaPayrollSummary['director_fee']),
//      // 4.
//      'PerOfCommFee' => $oaPayrollSummary['comm_fee'] > 0 ? $perOfEmp : '',
//      'AmtOfCommFee' => toCurrency($oaPayrollSummary['comm_fee']),
//      // 5.
//      'PerOfBonus' => $oaPayrollSummary['bonus'] > 0 ? $perOfEmp : '',
//      'AmtOfBonus' => toCurrency($oaPayrollSummary['bonus']),
//      // 6.
//      'PerOfBpEtc' => $oaPayrollSummary['bp_etc'] > 0 ? $perOfEmp : '',
//      'AmtOfBpEtc' => toCurrency($oaPayrollSummary['bp_etc']),
//      // 7.
//      'PerOfPayRetire' => $oaPayrollSummary['pay_retire'] > 0 ? $perOfEmp : '',
//      'AmtOfPayRetire' => $oaPayrollSummary['pay_retire'],
//      // 8.
//      'PerOfSalTaxPaid' => $perOfEmp,
//      'AmtOfSalTaxPaid' => $oaPayrollSummary['sal_tax_paid'],
//      // 9.
//      'PerOfEduBen' => $perOfEmp,
//      'AmtOfEduBen' => $oaPayrollSummary['edu_ben'],
//      // 10.
//      'PerOfGainShareOption' => $perOfEmp,
//      'AmtOfGainShareOption' => $oaPayrollSummary['gain_share_option'],
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
      // 12
//      'PerOfPension' => $perOfEmp,
//      'AmtOfPension' => $oaPayrollSummary['pension'],
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
      'Remarks' => ''
    ]);

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

    return (object) $result;
  }
}