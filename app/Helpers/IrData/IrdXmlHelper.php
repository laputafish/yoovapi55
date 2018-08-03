<?php namespace App\Helpers\IrData;

use App\Helpers\XmlValidatorHelper;

class IrdXmlHelper {
  public static function outputDataFile($outputFolder, $irdMaster, $irdInfo) {
    $irdCode = $irdInfo['irdForm']->ird_code;

    $schemaFile = storage_path('forms/'.strtolower($irdCode).'.xsd');
    $outputFilePath = $outputFolder.'/'.strtolower($irdCode).'.xml';
    $xml = new Ird56bXml($irdMaster, $irdInfo, $schemaFile);
    $xml->output($outputFilePath);

    // Copy scheme file
    $targetSchemaFile = $outputFolder.'/'.strtolower($irdCode).'.xsd';
    copy($schemaFile, $targetSchemaFile);

    $result = XmlValidatorHelper::validateFeeds($outputFilePath, $targetSchemaFile);
    return $result;
  }
}