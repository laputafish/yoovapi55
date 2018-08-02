<?php namespace App\Helpers\IrData;

class Ir56e extends IrBase {
  // Company
  public $section = '';
  public $ern = '';
  public $erName = '';
  public $erAddress = '';

  public $signatureName = '';
  public $designation = '';
  public $formDate = '01/05/2018';

  // Employee
  public $surname = '';
  public $givenName = '';
  public $nameInChinese = '';
  public $hkid;
  public $ppNum;
  public $gender = 'M';
  public $maritalStatus = 1; // 1=Single/Widowed/Divorced/Living Apart, 2=Married

  // Employee's Spouse
  public $spouseSurname = '';
  public $spouseGivenName = '';
  public $spouseHkid = '';
  public $spousePpNum = '';

  // Correspondence
  public $resAddress = '';
  public $posAddress = '';

  // Position
  public $capacity = 'clear';
  public $startDateOfEmp = '01/05/2018';
  public $monthlyFixedIncome = '0';
  public $monthlyAllowance = '0';

  // Place of residence
  public $addrOfPlace = '';
  public $natureOfPlace = '';
  public $rentPaidEr = 0;
  public $rentPaidEe = 0;
  public $rentRefund = 0;
  public $rentPaidErByEe = 0;

  // Non-Hong Kong Income
  public $overseaIncInd = 0; // 0 = not wholly or partly paid, 1 = yes
  public $amtPaidOverseaCo = 0;
  public $nameOfOverseaCo = '';
  public $addrOfOverseaCo = '';

  // share option
  public $shareBeforeEmp = 0; // 0 = no, 1 = yes

}