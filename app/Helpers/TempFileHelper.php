<?php namespace App\Helpers;

use App\Models\TempFile;

class TempFileHelper {
  public static function new ($filename, $userId)
  {
    $tempKey = md5(microtime().rand());
    return TempFile::create([
      'key'=>$tempKey,
      'label'=>pathinfo($filename, PATHINFO_FILENAME),
      'filename' => $tempKey.'.'.pathinfo($filename, PATHINFO_EXTENSION),
      'user_id' => $userId
    ]);
  }
}