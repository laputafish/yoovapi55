<?php namespace App\Helpers\IrData;

class Ird56bXml extends IrdBaseXml
{
  protected $irdMaster = null;
  protected $irdInfo = null;

  public function __construct($irdMaster, $irdInfo, $xsdFile)
  {
    parent::__construct('IR56B');
    $this->irdMaster = $irdMaster;
    $this->irdInfo = $irdInfo;
    $this->xsdFile = $xsdFile;
    $this->process();
  }

  private function process()
  {
    $this->addChild('Section', '6A1');
    $this->addChild('ERN', '01234561');
    $this->addChild('YrErReturn', 2014);
    $this->addChild('SubDate', '20140420');
    $this->addChild('ErName', 'ABCD COMPANY');
    $this->addChild('Designation', 'PARTNER');
    $this->addChild('NoRecordBatch', '00002');
    $this->addChild('TotIncomeBatch', '360000');
    for ($i = 0; $i < 10; $i++) {
      $employee = $this->addChild('Employee');
      $this->addChild('SheetNo', 3, $employee);
      $this->addChild('HKID', 'A1144556', $employee);
      $this->addChild('TypeOfForm', 'O', $employee);
      $this->addChild('Surname', 'AUYEUNG', $employee);
      $this->addChild('GivenName', 'TAI MAN', $employee);
      $this->addChild('NameInChinese', '歐陽大文', $employee);
      $this->addChild('Sex', 'M', $employee);
      $this->addChild('MaritalStatus', 2, $employee);
      $this->addChild('PpNum', '', $employee);
      $this->addChild('SpouseName', 'WONG, MEI MEI', $employee);
      $this->addChild('SpouseHKID', 'A456789A', $employee);
      $this->addChild('SpousePpNum', '', $employee);
      $this->addChild('ResAddr', 'Flat A, 8/F., 5 Mei Lai Road', $employee);
      $this->addChild('AreaCodeResAddr', 'K', $employee);
      $this->addChild('PosAddr', '', $employee);
      $this->addChild('Capacity', 'CLERK', $employee);
      $this->addChild('PtPrinEmp', '', $employee);
      $this->addChild('StartDateOfEmp', '20130401', $employee);
      $this->addChild('EndDateOfEmp', '20140331', $employee);
      $this->addChild('PerOfSalary', '20130401-20140331', $employee);
      $this->addChild('AmtOfSalary', 100000, $employee);
      $this->addChild('PerOfLeavePay', '', $employee);
      $this->addChild('AmtOfLeavePay', 0, $employee);
      $this->addChild('PerOfDirectorFee', '', $employee);
      $this->addChild('AmtOfDirectorFee', 0, $employee);
      $this->addChild('PerOfCommFee', '', $employee);
      $this->addChild('AmtOfCommFee', 0, $employee);
      $this->addChild('PerOfBonus', '', $employee);
      $this->addChild('AmtOfBonus', 0, $employee);
      $this->addChild('PerOfBpEtc', '', $employee);
      $this->addChild('AmtOfBpEtc', 0, $employee);
      $this->addChild('PerOfPayRetire', '', $employee);
      $this->addChild('AmtOfPayRetire', 0, $employee);
      $this->addChild('PerOfSalTaxPaid', 0, $employee);
      $this->addChild('AmtOfSalTaxPaid', 0, $employee);
      $this->addChild('PerOfEduBen', '', $employee);
      $this->addChild('AmtOfEduBen', 0, $employee);
      $this->addChild('PerOfGainShareOption', '', $employee);
      $this->addChild('AmtOfGainShareOption', 0, $employee);
      $this->addChild('NatureOtherRAP1', '', $employee);
      $this->addChild('PerOfOtherRAP1', '', $employee);
      $this->addChild('AmtOfOtherRAP1', 0, $employee);
      $this->addChild('NatureOtherRAP2', '', $employee);
      $this->addChild('PerOfOtherRAP2', '', $employee);
      $this->addChild('AmtOfOtherRAP2', 0, $employee);
      $this->addChild('NatureOtherRAP3', '', $employee);
      $this->addChild('PerOfOtherRAP3', '', $employee);
      $this->addChild('AmtOfOtherRAP3', 0, $employee);
      $this->addChild('PerOfPension', '', $employee);
      $this->addChild('AmtOfPension', 0, $employee);
      $this->addChild('TotalIncome', '0', $employee);
      $this->addChild('PlaceOfResInd', '0', $employee);
      $this->addChild('AddrOfPlace1', 0, $employee);
      $this->addChild('NatureOfPlace1', 0, $employee);
      $this->addChild('PerOfPlace1', 0, $employee);
      $this->addChild('RentPaidEr1', 0, $employee);
      $this->addChild('RentPaidEe1', 0, $employee);
      $this->addChild('RentRefund1', 0, $employee);
      $this->addChild('RentPaidErByEe1', 0, $employee);
      $this->addChild('AddrOfPlace2', 0, $employee);
      $this->addChild('NatureOfPlace2', 0, $employee);
      $this->addChild('PerOfPlace2', 0, $employee);
      $this->addChild('RentPaidEr2', 0, $employee);
      $this->addChild('RentPaidEe2', 0, $employee);
      $this->addChild('RentRefund2', 0, $employee);
      $this->addChild('RentPaidErByEe2', 0, $employee);

      $this->addChild('OverseaIncInd', '0', $employee);
      $this->addChild('AmtPaidOverseaCo', 0, $employee);
      $this->addChild('NameOfOverseaCo', 0, $employee);
      $this->addChild('AddrOfOverseaCo', 0, $employee);
      $this->addChild('Remarks', 0, $employee);
    }
  }

  public function output($outputFilePath)
  {
    $this->formatOutput = true;
    $this->save($outputFilePath);
  }

//  public static function outputXml($outputFolder) {
//    $outputFilePath = $outputFolder.' / ir56b . xml';
//    $ir56b = $dom->appendChild($dom->createElement('IR56B'));
//
//    self::addChild($dom, $ir56b, 'Section', '6A1');
//
//    $section = $ir56b->appendChild($dom->createElement('Section'));
//    $section->appendChild($dom->createTextNode('6A1'));
//
//    $ERN = $ir56b->appendChild($dom->createElement('ERN'));
//
//
//  }
//
//  public static function output($outputFilePath, $irdMaster, $irdInfo ) {
//    $dom = new DomDocument('1.0', 'UTF - 8');
//    $ir56b = $dom->appendChild('IR56B');
//    self::addAttributes($dom, $ir56b, )
//  }
}