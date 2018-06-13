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

  public static function duplicateDocument($document) {
    $newDocument = Document::create([]);
    $newMedia = MediaHelper::duplicateMedia( $document->media_id );

  }
//  public static function copyDocumentsToFolder($documentIds, $targetFolder) {
//    for($i=0; $i<count($documentIds); $i++) {
//      $document = Document::find($documentIds[$i]);
//      if(isset($document)) {
//        $document->folder_id = $targetFolder->id;
//        $document->save();
//      }
//    }
//  }

  public static function rename($id, $name) {
    $document = Document::find($id);
    $ext = end(explode(',', $name));
    $document->filename = $name;
    $document->file_type = strtolower($ext);
    $document->save();
  }
}