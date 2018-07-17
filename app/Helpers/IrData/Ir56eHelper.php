<?php namespace App\Helpers\IrData;

use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OAEmployeeHelper;

class Ir56eHelper extends IrDataHelper {
  public static function get($form, $formEmployee) {
    self::$form = $form;
    self::$formEmployee = $formEmployee;
    self::$team = $form->team;
    self::$oaAuth = OAHelper::refreshTokenByTeam(self::$team);

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

    $result->fileNo = $registrationNumber.'  ****';
    $result->ern = $ern;
    $result->erName = $oaTeam['name'];
    $result->erAddress = $oaTeam['setting']['companyAddress'];

    $result->signatureName = $form->signature_name;
    $result->designation = $form->designation;
    $result->formDate = phpDateFormat($form->form_date, 'd/m/Y');

    // Employee
    if(isset($oaEmployee)) {
      $result->name = $oaEmployee['displayName'].'  ****';
      $result->nameInChinese = getOAEmployeeChineseName($oaEmployee);
      $result->hkid = $oaEmployee['identityNumber'].'  ****';
      $result->ppNum = empty($oaEmployee['identityNumber']) ? $oaEmployee['passport'] : '';
      $result->gender = $oaEmployee['gender'].'  ****';
      $result->martialStatus = $oaEmployee['marital'] == 'married' ? 2 : 1;
      // 1=Single/Widowed/Divorced/Living Apart, 2=Married
    }
    // Employee's Spouse
    $result->spouseSurname = '';
    $result->spouseGivenName = '';
    $result->spouseHkid = '';
    $result->spousePpNum = '';

    // Correspondence
    $result->resAddress = $oaEmployee['address'][0]['text'];
    $result->posAddress = count($oaEmployee['address'])>1 ? $oaEmployee['address'][1] : trans('tax.same_as_above');

    // Position
    $result->capacity = $oaEmployee['jobTitle'];
    $joinedDate = phpDateFormat($oaEmployee['joinedDate'], 'Y-m-d');
    $result->startDateOfEmp = phpDateFormat($oaEmployee['joinedDate'], 'd/m/Y').'  ****';
    $result->monthlyFixedIncome = OAEmployeeHelper::getCommencementSalary($joinedDate, $oaSalaries).'  ****';
    $result->monthlyAllowance = '0';

    // Place of residence
    $result->placeProvided = '0  ****';
    $result->addrOfPlace = '';
    $result->natureOfPlace = '';
    $result->rentPaidEr = 0;
    $result->rentPaidEe = 0;
    $result->rentRefund = 0;
    $result->rentPaidErByEe = 0;

    // Non-Hong Kong Income
    $result->overseaIncInd = '0  ****'; // 0 = not wholly or partly paid, 1 = yes
    $result->amtPaidOverseaCo = 0;
    $result->nameOfOverseaCo = '';
    $result->addrOfOverseaCo = '';

    // share option
    $result->shareBeforeEmp = 0; // 0 = no, 1 = yes

    return $result;
  }
}