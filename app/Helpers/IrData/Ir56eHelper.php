<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56eHelper extends IrDataHelper {
  public static function get($team, $employeeId, $form=null) {
    self::$team = $team;
    self::$employeeId = $employeeId;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);


    if(isset($form)) {
      $signatureName = $form->signature_name;
      $designation = $form->designation;
      $formDate = $form->form_date;
    } else {
      $signatureName = $team->getSetting('default_signature_name', '(No signature name)');
      $designation = $team->getSetting('designation', '(No designation)');
      $formDate = date('Y-m-d');
    }

    $result = new Ir56e;

    // Grab data from OA
    $oaTeam = self::getOATeam();
    $oaEmployee = self::getOAAdminEmployee();
    $oaSalaries = self::getOASalary();

    // Company
    $registrationNumber = $oaTeam['setting']['registrationNumber'];
    $registrationNumberSegs = explode('-', $registrationNumber);

    $section = $registrationNumberSegs[0];
    $ern = $registrationNumberSegs[1];

    $result->fileNo = $registrationNumber;
    $result->ern = $ern;
    $result->erName = $oaTeam['name'];
    $result->erAddress = $oaTeam['setting']['companyAddress'];

    $result->signatureName = $signatureName;
    $result->designation = $designation;
    $result->formDate = phpDateFormat($formDate, 'd/m/Y');

    // Employee
    if(isset($oaEmployee)) {
      $result->name = $oaEmployee['displayName'];
      $result->nameInChinese = getOAEmployeeChineseName($oaEmployee);
      $result->hkid = $oaEmployee['identityNumber'];
      $result->ppNum = empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : '';
      $result->gender = $oaEmployee['gender'];
      $result->martialStatus = ($oaEmployee['marital'] == 'married' ? 2 : 1);
      // 1=Single/Widowed/Divorced/Living Apart, 2=Married
    }
    // Employee's Spouse
    $result->spouseName = '(spouse name)';
    $result->spouseHkid = '(spouse hkid)';
    $result->spousePpNum = '(spouse ppnum)';

    // Correspondence
    $result->resAddress = $oaEmployee['address'][0]['text'];
    $result->posAddress = count($oaEmployee['address'])>1 ? $oaEmployee['address'][1] : trans('tax.same_as_above');

    // Position
    $result->capacity = strtoupper( $oaEmployee['jobTitle'] );
    $joinedDate = phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d');
    $result->startDateOfEmp = phpDateFormat($oaEmployee['joinedDate'], 'd/m/Y');
    $result->monthlyFixedIncome = toCurrency(OAEmployeeHelper::getCommencementSalary($joinedDate, $oaSalaries));
    $result->monthlyAllowance = toCurrency(110 );
    $result->fluctuatingIncome = toCurrency(120);

    // Place of residence
    $result->placeProvided = toCurrency(0);
    $result->addrOfPlace = '(address of place)';
    $result->natureOfPlace = '(nature of place)';
    $result->rentPaidEr = 1;
    $result->rentPaidEe = 2;
    $result->rentRefund = 3;
    $result->rentPaidErByEe = 4;

    // Non-Hong Kong Income
    $result->overseaIncInd = 0; // 0 = not wholly or partly paid, 1 = yes
    $result->amtPaidOverseaCo = '';
    $result->nameOfOverseaCo = '(non Hong Kong comapny)';
    $result->addrOfOverseaCo = '(non Hong Kong company address)';

    // share option
    $result->shareBeforeEmp = 0; // 0 = no, 1 = yes

    return $result;
  }
}