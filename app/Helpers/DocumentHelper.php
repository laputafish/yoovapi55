<?php
namespace App\Helpers;

class DocumentHelper {
  public static function getFileTypeFolder($file) {
    $extension = strtolower( $file->getExtension() );
    if(in_array($extension, ['png','gif','jpg','jpeg'])) {
      return 'image';
    }
    else {
      return 'doc';
    }
  }
  
}