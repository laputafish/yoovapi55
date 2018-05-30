<?php
namespace App\Helpers;

use App\Models\Folder;
use App\Models\Equipment;
use App\Models\Document;

class FolderHelper {

  public static function createNewDocument( $file ) {
    $media = MediaHelper::createMedia($file);
    $folder = self::getScannerFolder();
    $document = Document::create([
      'media_id'=>$media->id,
      'filename'=>$file->getFilename(),
      'folder_id'=>$folder->id,
      'file_type'=>$file->getExtension()
    ]);
    return $document;
  }

  public static function underPublic($id) {
    $publicFolder = Folder::whereName('public')->first();
    $ancestorIds = Folder::ancestorsOf($id)->pluck('id')->toArray();
    return in_array( $publicFolder->id, $ancestorIds );
  }

  public static function getUserAncestors($id) {
    $usersFolder = Folder::whereName('users')->first();
    return Folder::where('id','>',$usersFolder->id)->defaultOrder()->ancestorsAndSelf( $id );
  }

  public static function getScannerFolder() {
    $scanner = Equipment::whereName('scanner')->first();
    if($scanner->occupied_by == 0) {
      return self::getPublicScanFolder();
    }
    else {
      return $scanner->occupied_by_user->scan_folder;
    }
  }

  public static function getPublicScanFolder() {
      $publicFolder = Folder::whereName('public')->first();
      $scanFolder = $publicFolder->descendants()->whereName('scan')->first();
      return $scanFolder;
  }

  public static function createUserFolder( $user ) {
    $usersFolder = Folder::whereName('users')->first();
    $userFolder = $usersFolder->descendants()->whereOwnedBy($user->id)->first();
    if(is_null($userFolder)) {
      $userFolder = Folder::create([
        'name'=>$user->name,
        'owned_by'=>$user->id
      ]);
      $userFolder->appendToNode( $usersFolder );
      $userFolder->save();

      $scan = Folder::create([
        'name'=>'scan',
        'description'=>'Scan',
        'is_system'=>1,
        'writable'=>1,
        'owned_by'=>$user->id]);
      $scan->appendToNode($userFolder);
      $scan->save();

      $meetings = Folder::create([
        'name'=>'meetings',
        'description'=>'Meetings',
        'is_system'=>1,
        'writable'=>0,
        'owned_by'=>$user->id]);
      $meetings->appendToNode($userFolder);
      $meetings->save();

      $myDocuments = Folder::create([
        'name'=>'my_documents',
        'description'=>'My Documents',
        'is_system'=>1,
        'writable'=>1,
        'owned_by'=>$user->id]);
      $myDocuments->appendToNode($userFolder);
      $myDocuments->save();
    }
    return $userFolder;
  }
}