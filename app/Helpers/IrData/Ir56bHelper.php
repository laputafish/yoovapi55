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

    // Company
    $registrationNumber = $oaTeam['setting']['registrationNumber'];
    $registrationNumberSegs = explode('-', $registrationNumber);
    $section = $registrationNumberSegs[0];
    $ern = $registrationNumberSegs[1];

    $headerPeriod = 'for the year from 1 April ' . ($fiscalYear - 1) . ' to 31 March ' . ($fiscalYear);
    $result = [
      'Section' => $section,
      'Ern' => $ern,
      'YrErReturn' => $form->fiscal_year,
      'SubDate' => phpDateFormat($formDate, 'd/m/Y'),
      'ErName' => $oaTeam['name'],
      'Designation' => $designation,
      'NoRecordBatch' => isset($form) ? $form->employees->count() : 1,
      'TotIncomeBatch' => isset($formSummary) ? $formSummary['totalEmployeeIncome'] : 0,

      // Non-ird fields
      'HeaderPeriod' => strtoupper($headerPeriod),
      'EmpPeriod' => $headerPeriod . ':',

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
    $perOfEmp = str_replace('-', '', $empStartDate) . '-' .
      str_replace('-', '', $empEndDate);

    $result = array_merge($result, [
      // Non-ird fields
      'FileNo' => $registrationNumber,
      'PerOfEmp' => $perOfEmp,

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
      'ResAddress' => $oaEmployee['address'][0]['text'],
      'AreaCodeResAddr' => $defaults['areaCodeResAddr'],
      'PosAddr' => count($oaEmployee['address']) > 1 ? $oaEmployee['address'][1] : trans('tax.same_as_above'),

      // Position
      'Capacity' => strtoupper($oaEmployee['jobTitle']),
      'PtPrinEmp' => $defaults['ptPrinEmp'],

      'StartDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
      'EndDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y'),

      // Income Particulars
      // 1.
      'PerOfSalary' => $perOfEmp, // 20170401-20180331
      'AmtOfSalary' => $oaPayrollSummary->amtOfSalary,
      // 2.
      'PerOfLeavePay' => $perOfEmp,
      'AmtOfLeavePay' => $oaPayrollSummary->amtOfLeavePay,
      // 3.
      'PerOfDirectorFee' => $perOfEmp,
      'AmtOfDirectorFee' => $oaPayrollSummary->amtOfDirectorFee,
      // 4.
      'PerOfCommFee' => $perOfEmp,
      'AmtOfCommFee' => $oaPayrollSummary->amtOfCommFee,
      // 5.
      'PerOfBonus' => $perOfEmp,
      'AmtOfBonus' => $oaPayrollSummary->amtOfBonus,
      // 6.
      'PerOfBpEtc' => $perOfEmp,
      'AmtOfBpEtc' => $oaPayrollSummary->amtOfBpEtc,
      // 7.
      'PerOfPayRetire' => $perOfEmp,
      'AmtOfPayRetire' => $oaPayrollSummary->amtOfPayRetire,
      // 8.
      'PerOfSalTaxPaid' => $perOfEmp,
      'AmtOfSalTaxPaid' => $oaPayrollSummary->amtOfSalTaxPaid,
      // 9.
      'PerOfEduBen' => $perOfEmp,
      'AmtOfEduBen' => $oaPayrollSummary->amtOfEduBen,
      // 10.
      'PerOfGainShareOption' => $perOfEmp,
      'AmtOfGainShareOption' => $oaPayrollSummary->amtOfGainShareOption,
      // 11.1
      'NatureOtherRAP1' => '',
      'PerOfOtherRAP1' => '',
      'AmtOfOtherRAP1' => '',
      // 11.2
      'NatureOtherRAP2' => '',
      'PerOfOtherRAP2' => '',
      'AmtOfOtherRAP1' => '',
      // 11.3
      'NatureOtherRAP3' => '',
      'PerOfOtherRAP3' => '',
      'AmtOfOtherRAP1' => '',
      // 12
      'PerOfPension' => $perOfEmp,
      'AmtOfPension' => $oaPayrollSummary->amtOfPension,
      // total
      'TotalIncome' => $oaPayrollSummary->totalIncome,
      
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

    if (count($oaPayrollSummary->otherRaps) > 0) {
      $result['NatureOtherRAP1'] = $oaPayrollSummary->otherRaps[0]['natureOtherRAP1'];
      $result['PerOfOtherRAP1'] = $perOfEmp;
      $result['AmtOfOtherRAP1'] = $oaPayrollSummary->otherRaps[0]['amtOfOtherRAP1'];
    }

    if (count($oaPayrollSummary->otherRaps) > 1) {
      $result['NatureOtherRAP2'] = $oaPayrollSummary->otherRaps[1]['natureOtherRAP1'];
      $result['PerOfOtherRAP2'] = $perOfEmp;
      $result['AmtOfOtherRAP2'] = $oaPayrollSummary->otherRaps[1]['amtOfOtherRAP1'];
    }

    if (count($oaPayrollSummary->otherRaps) > 2) {
      $result['NatureOtherRAP3'] = $oaPayrollSummary->otherRaps[2]['natureOtherRAP3'];
      $result['PerOfOtherRAP3'] = $perOfEmp;
      $result['AmtOfOtherRAP3'] = $oaPayrollSummary->otherRaps[2]['amtOfOtherRAP1'];
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