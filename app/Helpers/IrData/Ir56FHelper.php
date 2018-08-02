<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56FHelper extends IrDataHelper {

  public static function get($team, $employeeId, $form=null, $options=[]) {

    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

    if(isset($form)) {
      $signatureName = $form->signature_name;
      $designation = $form->designation;
      $formDate = $form->form_date;
      $fiscalYearStart = ($form->fiscal_year-1).'-04-01';
    } else {
      $signatureName = $team->getSetting('default_signature_name', '(No signature name)');
      $designation = $team->getSetting('designation', '(No designation)');
      $formDate = date('Y-m-d');
      $fiscalYearStart = getCurrentFiscalYearStartDate();
    }

    // Grab data from OA
    $oaTeam = self::getOATeam();
    $oaEmployee = self::getOAAdminEmployee();
    $oaSalaries = self::getOASalary();

    // Company
    $registrationNumber = $oaTeam['setting']['registrationNumber'];
    $registrationNumberSegs = explode('-', $registrationNumber);

    $section = $registrationNumberSegs[0];
    $ern = $registrationNumberSegs[1];

    $result = [
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
      $result = array_merge($result, [
        'name' => $oaEmployee['lastName'].', '.$oaEmployee['firstName'],
        'surname' => $oaEmployee['lastName'],
        'nameInChinese' => getOAEmployeeChineseName($oaEmployee),
        'hkid' => $oaEmployee['identityNumber'],
        'ppNum' => empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : 'xxxxx',
        'gender' => $oaEmployee['gender'],
        'maritalStatus' => ($oaEmployee['marital'] == 'married' ? 2 : 1),
        // 1=Single/Widowed/Divorced/Living Apart, 2=Married

        'endDateOfEmp' => $jobEndedDate,
        'fiscalYearStartDateBeforeCease' => $fiscalYearStartBeforeCease
      ]);
    }


    $result = array_merge($result, [
      // Employee's Spouse
      'spouseName' => '(spouse name)',
      'spouseHkid' => '(spouse hkid)',
      'spousePpNum' => '(spouse ppnum)',

      // Correspondence
      'resAddress' => $oaEmployee['address'][0]['text'],
      'posAddress' => count($oaEmployee['address'])>1 ? $oaEmployee['address'][1] : trans('tax.same_as_above'),

      // Position
      'capacity' => strtoupper( $oaEmployee['jobTitle'] ),
      'startDateOfEmp' => phpDateFormat($oaEmployee['joinedDate'], 'd/m/Y'),
      'monthlyFixedIncome' => toCurrency(OAEmployeeHelper::getCommencementSalary(
        phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d'), $oaSalaries)),
      'monthlyAllowance' => toCurrency(110 ),
      'fluctuatingIncome' => toCurrency(120),

      // Place of residence
      'placeProvided' => toCurrency(0),
      'addrOfPlace' => '(address of place)',
      'natureOfPlace' => '(nature of place)',
      'rentPaidEr' => 1,
      'rentPaidEe' => 2,
      'rentRefund' => 3,
      'rentPaidErByEe' => 4,

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