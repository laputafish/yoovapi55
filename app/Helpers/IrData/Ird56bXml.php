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
    $this->addChild('Section', $this->irdMaster['Section']);
    $this->addChild('ERN', $this->irdMaster['ERN']);
    $this->addChild('YrErReturn', $this->irdMaster['YrErReturn']);
    // irdDate: 'dd/mm/yyyy'
    // numberDate: 'yyyymmdd
    $this->addChild('SubDate', irdDate2numberDate($this->irdMaster['SubDate']));
    $this->addChild('ErName', $this->irdMaster['ErName']);
    $this->addChild('Designation', $this->irdMaster['Designation']);
    $this->addChild('NoRecordBatch', str_pad($this->irdMaster['NoRecordBatch'],5, '0', STR_PAD_LEFT));
    $this->addChild('TotIncomeBatch', str_replace(',','', $this->irdMaster['TotIncomeBatch']));
    for ($i = 0; $i < count($this->irdMaster['Employees']); $i++) {
      $employeeData = $this->irdMaster['Employees'][$i];

      $employee = $this->addChild('Employee');
      $fields = [
        'SheetNo',
        'HKID',
        'TypeOfForm',
        'Surname',
        'GivenName',
        'NameInChinese',
        'Sex',
        'MaritalStatus',
        'PpNum',
        'SpouseName',
        'SpouseHKID',
        'SpousePpNum',
        'ResAddr',
        'AreaCodeResAddr',
        'PosAddr',
        'Capacity',
        'PtPrinEmp',
        'StartDateOfEmp',
        'EndDateOfEmp',
        'PerOfSalary',
        'AmtOfSalary',
        'PerOfLeavePay',
        'AmtOfLeavePay',
        'PerOfDirectorFee',
        'AmtOfDirectorFee',
        'PerOfCommFee',
        'AmtOfCommFee',
        'PerOfBonus',
        'AmtOfBonus',
        'PerOfBpEtc',
        'AmtOfBpEtc',
        'PerOfPayRetire',
        'AmtOfPayRetire',
        'PerOfSalTaxPaid',
        'AmtOfSalTaxPaid',
        'PerOfEduBen',
        'AmtOfEduBen',
        'PerOfGainShareOption',
        'AmtOfGainShareOption',

        'NatureOtherRAP1',
        'PerOfOtherRAP1',
        'AmtOfOtherRAP1',
        'NatureOtherRAP2',
        'PerOfOtherRAP2',

        'AmtOfOtherRAP2',
        'NatureOtherRAP3',
        'PerOfOtherRAP3',
        'AmtOfOtherRAP3',
        'PerOfPension',
        'AmtOfPension',
        'TotalIncome',
        'PlaceOfResInd',
        'AddrOfPlace1',
        'NatureOfPlace1',
        'PerOfPlace1',
        'RentPaidEr1',
        'RentPaidEe1',
        'RentRefund1',
        'RentPaidErByEe1',
        'AddrOfPlace2',
        'NatureOfPlace2',
        'PerOfPlace2',
        'RentPaidEr2',
        'RentPaidEe2',
        'RentRefund2',
        'RentPaidErByEe2',

        'OverseaIncInd',
        'AmtPaidOverseaCo',
        'NameOfOverseaCo',
        'AddrOfOverseaCo',
        'Remarks'
      ];
      foreach($fields as $field) {
        switch($field) {
          case 'HKID':
          case 'SpouseHKID':
            $value = preg_replace('/[\)\(]/', '', $employeeData[$field] );
            break;
          default:
            $value = $employeeData[$field];
            break;
        }
        $this->addChild($field, $value, $employee);
      }
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