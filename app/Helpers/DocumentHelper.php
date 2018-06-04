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

  public static function moveDocumentsToFolder($documentIds, $targetFolder) {
    for($i=0; $i<count($documentIds); $i++) {
      $document = Document::find($documentIds[$i]);
      if(isset($document)) {
        $document->folder_id = $targetFolder->id;
        $document->save();
      }
    }
  }

  public static function copyDocumentsToFolder($documentIds, $targetFolder) {
    for($i=0; $i<count($documentIds); $i++) {
      $document = Document::find($documentIds[$i]);
      if(isset($document)) {
        $document->folder_id = $targetFolder->id;
        $document->save();
      }
    }
  }
  
}