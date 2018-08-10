<?php namespace App\Helpers;

class ZipHelper {

  public static function downloadFiles($allFiles) {
    $zipFileName = storage_path('app/teams/31e44b9c-a823-4eb3-aaa8-24fb2bc7d9dc/application_letters/6/zipped.zip');
    $zip = new \ZipArchive;
    if(file_exists($zipFileName)) {
      unlink($zipFileName);
    }
    if( $zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
      exit("cannot open <$zip>\n");
    }

    foreach($allFiles as $fileItem) {
      $zip->addFile($fileItem['source'], $fileItem['custom']);
    }
    $zip->close();
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename = '".pathinfo($zipFileName, PATHINFO_BASENAME)."'");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("$zipFileName");
  }
}