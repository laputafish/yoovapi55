<?php namespace App\Helpers\IrData;

use App\Helpers\XmlValidatorHelper;

class IrdXmlHelper {
  public static function outputDataFile($outputFolder, $irdMaster, $irdInfo, &$messages) {
    $irdCode = $irdInfo['irdForm']->ird_code;
    $schemaFile = storage_path('forms/'.strtolower($irdCode).'.xsd');
    $outputFilePath = $outputFolder.'/'.strtolower($irdCode).'.xml';

    $className = '\\App\Helpers\\IrData\\Ird56'.substr(strtolower($irdCode),-1).'Xml';
    $xml =  new $className($irdMaster, $irdInfo, $schemaFile);

//    $irdDataHelperClassName = '\\App\\Helpers\\IrData\\' .
//      camelize(strtolower($irdForm->ird_code)) .
//      ($isSample ? 'Sample' : '') .
//      'Helper';
//    $irdEmployee = $irdDataHelperClassName
//
//
//
//    $xml = new Ird56bXml($irdMaster, $irdInfo, $schemaFile);

    $xml->output($outputFilePath);

    // Copy scheme file
    $targetSchemaFile = $outputFolder.'/'.strtolower($irdCode).'.xsd';
    copy($schemaFile, $targetSchemaFile);

//    $result = true;
    $result = XmlValidatorHelper::validateFeeds($outputFilePath, $targetSchemaFile);
//    if(!$result) {
//      echo 'IrdXmlHelper :: xml file has some error.'; nf();
//      $messages = $xml->validate();
//    }
    return $result;
  }
}