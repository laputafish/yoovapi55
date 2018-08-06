<?php namespace App\Helpers\IrData;

class Ird56mXml extends IrdBaseXml
{
  protected $irdMaster = null;
  protected $irdInfo = null;

  public function __construct($irdMaster, $irdInfo, $xsdFile)
  {
    parent::__construct($irdInfo['irdForm']->ird_code);
    $this->irdMaster = $irdMaster;
    $this->irdInfo = $irdInfo;
    $this->xsdFile = $xsdFile;
    $this->process();
  }

  private function process()
  {
    $this->addChild('Section', $this->irdMaster['Section']);
    $this->addChild('ERN', $this->irdMaster['ERN']);
    $this->addChild('AssYr', $this->irdMaster['YrErReturn']);
    // irdDate: 'dd/mm/yyyy'
    // numberDate: 'yyyymmdd
    $this->addChild('SubDate', irdDate2numberDate($this->irdMaster['SubDate']));
    $this->addChild('PayerName', $this->irdMaster['ErName']);
    $this->addChild('Designation', $this->irdMaster['Designation']);
    $this->addChild('NoRecordBatch', str_pad($this->irdMaster['NoRecordBatch'],5, '0', STR_PAD_LEFT));
    $this->addChild('TotIncomeBatch', str_replace(',','', $this->irdMaster['TotIncomeBatch']));
    for ($i = 0; $i < count($this->irdMaster['Employees']); $i++) {
      $employeeData = $this->irdMaster['Employees'][$i];

      $employee = $this->addChild('Recipient');
      $fields = [
        'SheetNo',
        'ComRecNameEng',
        'ComRecNameChi',
        'ComRecBRN',
        'HKID',
        'NameInEnglish',
        'NameInChinese',
        'Sex',
        'MaritalStatus',
        'SpouseName',
        'SpouseHKID',
        'SpousePpNum',
        'PosAddr',
        'AreaCodePosAddr',
        'PhoneNum',
        'Capacity',
        'StartDateOfService',
        'EndDateOfService',
        'AmtOfType1',
        'AmtOfType2',
        'AmtOfType3',
        'AmtOfArtistFee',
        'AmtOfCopyright',
        'AmtOfConsultFee',
        'NatureOtherInc1',
        'AmtOfOtherInc1',
        'NatureOtherInc2',
        'AmtOfOtherInc2',
        'TotalIncome',
        'IndOfSumWithheld',
        'AmtOfSumWithheld',
        'IndOfRemark',
        'Remarks'
      ];
      foreach($fields as $field) {
        switch($field) {
          case 'AmtOfType1':
          case 'AmtOfType2':
          case 'AmtOfType3':
          case 'AmtOfArtistFee':
          case 'AmtOfCopyright':
          case 'AmtOfConsultFee':
          case 'NatureOtherInc1':
          case 'AmtOfOtherInc1':
          case 'NatureOtherInc2':
          case 'AmtOfOtherInc2':
          case 'TotalIncome':
          case 'IndOfS`umWithheld':
          case 'AmtOfSumWithheld':
            $value = str_replace(',', '', $employeeData[$field]);
            break;

          case 'StartDateOfService':
          case 'EndDateOfService':
            $value = irdDate2numberDate($employeeData[$field]);
            break;
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