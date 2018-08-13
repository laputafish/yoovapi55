<?php namespace App\Helpers;

class DownloadHelper {
  public static function download($filePath, $filename) {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    header("Content-type: application/".$extension);
    header("Content-Disposition: attachment; filename = '".$filename."'");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($filePath);
  }

  public static function show($filePath, $filename) {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    header("Content-type: application/".$extension);
    header("Content-Disposition: inline; filename = '".$filename."'");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($filePath);
  }


}