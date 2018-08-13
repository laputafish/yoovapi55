<?php namespace App\Helpers;

class ZipHelper {

  public static function downloadFile($filePath, $caption='') {
    if(empty($caption)) {
      $caption = pathinfo($filePath, PATHINFO_FILENAME);
    }
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename = '".
      $caption.'.'.pathinfo($filePath, PATHINFO_EXTENSION)."'");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($filePath );
  }

  public static function downloadFiles($allFiles, $zipFileName) {
    self::createFile($allFiles, $zipFileName);
    self::downloadFile($zipFileName);
  }

  public static function createFile($allFiles, $zipFilename) {
    $zip = new \ZipArchive;
    if(file_exists($zipFilename)) {
      unlink($zipFilename);
    }
    if( $zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
      exit("cannot open <$zip>\n");
    }

    foreach($allFiles as $fileItem) {
      $zip->addFile($fileItem['source'], $fileItem['custom']);
    }
    $zip->close();
  }
}