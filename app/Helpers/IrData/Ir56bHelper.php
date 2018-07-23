<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56bHelper extends IrDataHelper {

  public static function get($team, $employeeId, $form=null, $options=[]) {
    $defaults = array_key_exists('defaults', $options) ? $options['defaults'] : [];

    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    $sheetNo = 1;
    if(isset($form)) {
      $signatureName = $form->signature_name;
      $designation = $form->designation;
      $formDate = $form->form_date;
      $fiscalYearStart = ($form->fiscal_year-1).'-04-01';
      if(array_key_exists('sheetNo', $options)) {
        $sheetNo = $options['sheetNo'];
      }
    } else {
      $signatureName = $team->getSetting('default_signature_name', '(No signature name)');
      $designation = $team->getSetting('designation', '(No designation)');
      $formDate = date('Y-m-d');
      $fiscalYearStart = getCurrentFiscalYearStartDate();
    }
    $fiscalYear = (int) substr($fiscalYearStart, 0, 4);

    $fiscalYearPeriod = [
      'startDate' => $fiscalYear.'-04-01',
      'endDate' => ($fiscalYear+1).'-03-31'
    ];

    // Grab data from OA
    $oaTeam = self::getOATeam();
    $oaEmployee = self::getOAAdminEmployee();
    $oaSalaries = self::getOASalary();
    $oaPayrolls = self::getPayrolls();

    $joinedDate = substr($oaEmployee['joinedDate'], 0, 10);
    $jobEndedDate = substr($oaEmployee['jobEndedDate'], 0, 10);

    $empStartDate = $fiscalYearPeriod[0] > $joinedDate ? $fiscalYearPeriod[0] : $joinedDate;
    $empEndDate = isset($oaEmployee['jobEndedDate']) ?
      ($fiscalYearPeriod[1] < $jobEndedDate ? $fiscalYearPeriod[1] : $jobEndedDate) :
      $fiscalYearPeriod[1];

    // Company
    $registrationNumber = $oaTeam['setting']['registrationNumber'];
    $registrationNumberSegs = explode('-', $registrationNumber);

    $section = $registrationNumberSegs[0];
    $ern = $registrationNumberSegs[1];

    $headerPeriod = 'for the year from 1 April '.($fiscalYear - 1).' to 31 March '.($fiscalYear);
    $result = [
      'headerPeriod' => strtoupper( $headerPeriod ),
      'empPeriod' => $headerPeriod.':',
      'sheetNo' => $sheetNo,
      'fileNo' => $registrationNumber,
      'ern' => $ern,
      'erName' => $oaTeam['name'],
      'erAddress' => $oaTeam['setting']['companyAddress'],
      'signatureName' => $signatureName,
      'designation' => $designation,
      'formDate' => phpDateFormat($formDate, 'd/m/Y')
    ];

    // Employee
    if(isset($oaEmployee)) {
      if(isset($oaEmployee['jobEndedDate'])) {
        $jobEndedDate = phpDateFormat($oaEmployee['jobEndedDate'],'d/m/Y');
        $fiscalYearStartBeforeCease = phpDateFormat(getFiscalYearStartOfDate($jobEndedDate), 'd/m/Y');
      } else {
        $jobEndedDate = '';
        $fiscalYearStartBeforeCease = '';
      }

      // 1=Single/Widowed/Divorced/Living Apart, 2=Married
      $martialStatus = ($oaEmployee['marital'] == 'married' ? 2 : 1);

      $result = array_merge($result, [
        'givenName' => $oaEmployee['firstName'],
        'name' => $oaEmployee['lastName'].', '.$oaEmployee['firstName'],
        'surname' => $oaEmployee['lastName'],
        'nameInChinese' => getOAEmployeeChineseName($oaEmployee),
        'hkid' => $oaEmployee['identityNumber'],
        'ppNum' => empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : '',
        'gender' => $oaEmployee['gender'],
        'martialStatus' => $martialStatus,

        'startDateOfEmp' => phpDateFormat($empStartDate, 'd/m/Y'),
        'endDateOfEmp' => phpDateFormat($empEndDate, 'd/m/Y')
      ]);
    }

    $normalPeriodStr = str_replace('-', '', $empStartDate).'-'.
      str_replace('-', '', $empEndDate);

    $result = array_merge($result, [
      // Employee's Spouse
      'spouseName' => $martialStatus == 1 ? '' : $defaults['spouseName'],
      'spouseHkid' => $martialStatus == 1 ? '' : $defaults['spouseHkid'],
      'spousePpNum' => $martialStatus == 1 ? '' : $defaults['spousePpNum'],

      // Correspondence
      'resAddress' => $oaEmployee['address'][0]['text'],
      'areaCodeResAddr' => $defaults['areaCodeResAddr'],
      'posAddress' => count($oaEmployee['address'])>1 ? $oaEmployee['address'][1] : trans('tax.same_as_above'),

      // Position
      'capacity' => strtoupper( $oaEmployee['jobTitle'] ),
      'ptPrinEmp' => $defaults['ptPrinEmp'],

      'monthlyFixedIncome' => toCurrency(OAEmployeeHelper::getCommencementSalary(
        phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d'), $oaSalaries)),
      'monthlyAllowance' => toCurrency(110 ),
      'fluctuatingIncome' => toCurrency(120),

      // Place of residence
//      'placeProvided' => toCurrency(0),
//      'addrOfPlace' => '(address of place)',
//      'natureOfPlace' => '(nature of place)',
//      'rentPaidEr' => 1,
//      'rentPaidEe' => 2,
//      'rentRefund' => 3,
//      'rentPaidErByEe' => 4,

      // Income Particulars
      'perOfSalary' => $normalPeriodStr, // 20170401-20180331
      'AmtOfSalary' =>
      // Place of residence


      // Non-Hong Kong Income
      'overseaIncInd' => 1, // 0' => not wholly or partly paid, 1' => yes
      'amtPaidOverseaCo' => '9999',
      'nameOfOverseaCo' => '(non Hong Kong comapny)',
      'addrOfOverseaCo' => '(non Hong Kong company address)',

      // share option
      'shareBeforeEmp' => 0, // 0' => no, 1' => yes
    ]);
    return (object) $result;
  }
}