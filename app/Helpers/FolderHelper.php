<?php
namespace App\Helpers;

use App\Models\Folder;
use App\Models\Equipment;
use App\Models\Document;
use App\User;

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

  public static function moveItemsToFolder($targetFolderId, $documentIds, $folderIds) {
    $targetFolder = Folder::find($targetFolderId);
    if(isset($targetFolder)) {
      DocumentHelper::moveDocumentsToFolder($documentIds, $targetFolder);
      self::moveFoldersToFolder($folderIds, $targetFolder);
    }
  }

  public static function moveFoldersToFolder($folderIds, $targetFolder) {
    for($i=0; $i<count($folderIds); $i++) {
      $folder = Folder::find($folderIds[$i]);
      if(isset($folder)) {
        $folder->appendToNode($targetFolder);
        $folder->save();
      }
    }
  }

  public static function copyFoldersToFolder($folderIds, $targetFolder) {
    $folders = Folder::inWhere('id', $folderIds);
    self::appendContent( $targetFolder, $folders, []);
//    for($i=0; $i<count($folderIds); $i++) {
//      $folder = Folder::find($folderIds[$i]);
//      $newFolder = Folder::create([]);
//      $newFolder->name = $folder->name;
//      $newFolder->description = $folder->description;
//      $newFolder->remark = $folder->remark;
//      $newFolder->appendToNode($targetFolder->id);
//      $newFolder->save();
//      self::appendContent( $newFolder, $folder->children, $folder->documents);
//    }
  }

  public static function appendContent( $targetFolder, $folders, $documents ) {
    foreach($folders as $folder) {
      $newFolder = Folder::create([]);
      $newFolder->name = $folder->name;
      $newFolder->description = $folder->description;
      $newFolder->remark = $folder->remark;
      $newFolder->appendToNode($targetFolder->id);
      $newFolder->save();
      self::appendContent( $newFolder, $folder->children, $folder->documents);
    }
    foreach($documents as $document) {
      $document = DocumentHelper::duplicateDocument($document);
      $document->folder()->save($targetFolder);
    }
  }

  public static function getDocumentCount($folderId) {
    return Document::whereFolderId( $folderId)->count();
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

  public static function getAncestors($id) {
    $rootFolder = Folder::whereName('root')->first();
    $folder = Folder::find($id);

    $ancestors = array_values( Folder::ancestorsAndSelf($id)->where('id','>',$rootFolder->id)->toArray() );
//    $result = Folder::where('id', '>', $rootFolder->id)->defaultOrder()->ancestorsAndSelf($id);
//    $ancestors = $result->toArray();
    if($ancestors[0]['name'] == 'users') {
      array_shift($ancestors);
    }
    return $ancestors;
  }
  public static function getPublicAncestors($id) {
    $publicFolder = Folder::whereName('public')->first();
    return Folder::where('id','>=',$publicFolder->id)->defaultOrder()->ancestorsAndSelf( $id );
  }

  public static function getScannerFolder() {
    $scanner = Equipment::whereName('scanner')->first();
    if($scanner->occupied_by == 0) {
      return self::getPublicScanFolder();
    }
    else {
      return $scanner->holdByUser->scan_folder;
    }
  }

  public static function getSharedFolders() {
    return [];
  }
  public static function getPersonalFolders($userId) {
    $user = User::find($userId);
    return self::transformFolders([$user->folder]);
  }
  public static function transformFolders($folders) {
    $result = [];
    if(isset($folders)) {
      foreach ($folders as $folder) {
        $result[] = [
          'id' => $folder->id,
          'name' => $folder->name,
          'expanded' => true,
          'children' =>  self::transformFolders($folder->children)
        ];
      }
    }
    return $result;
  }

  public static function getPublicFolder() {
    $publicFolder = Folder::whereName('public')->first();
    $publicFolders = Folder::descendantsAndSelf($publicFolder->id)->toTree();
    return self::transformFolders($publicFolders);
  }

  public static function getPublicFolders() {
    $publicFolder = Folder::whereName('public')->first();
    $publicFolders = $publicFolder->descendants()->get()->toTree();
    return self::transformFolders($publicFolders);
  }

  public static function getPublicScanFolder() {
      $publicFolder = Folder::whereName('public')->first();
      $scanFolder = $publicFolder->descendants()->whereName('scan')->first();
      return $scanFolder;
  }

  public static function newFolder($parentFolder) {
    $folderName = 'folder_';
    $count = 1;
    $newFolderName = $folderName.($count<10 ? '0'.$count : $count);
    while(self::fileExists($parentFolder, $newFolderName)) {
      $count++;
      $newFolderName = $folderName.($count<10 ? '0'.$count : $count);
    }
    $newFolder = Folder::create([
      'name'=>$newFolderName,
      'description'=>$newFolderName
    ]);
    $newFolder->appendToNode($parentFolder);
    $newFolder->save();
    return $newFolder;
  }

  public static function fileExists($parentFolder, $folderName) {
    $result = false;
    if(isset($parentFolder->children)) {
      for($i=0; $i<count($parentFolder->children); $i++) {
        if (strtolower($parentFolder->children[$i]->name) == strtolower($folderName)) {
          $result = true;
          break;
        }
      }
    }
    return $result;
  }

  public static function checkCreateFolders($folderPath) {
    if(!file_exists($folderPath)) {
      mkdir($folderPath, 0776, true);
    }
  }

  public static function checkCreate( $folderName, $folderDescription, $parentFolder ) {
    $targetFolder = null;
    for($i=0; $i<count($parentFolder->children); $i++) {
      $child = $parentFolder->children[$i];
      if($child->name === $folderName) {
        $targetFolder = $child;
        break;
      }
    }
    if(is_null($targetFolder)) {
      $targetFolder = Folder::create([
        'name' => $folderName,
        'description' => $folderDescription
      ]);
      $targetFolder->appendToNode($parentFolder);
    }
    return $targetFolder;
  }

  public static function createFolder( $folderName, $folderDescription, $parentFolder ) {
    $newFolder = Folder::create([
      'name'=>$folderName,
      'description'=>$folderDescription
    ]);
    $newFolder->appendToNode( $parentFolder );
    return $newFolder;
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

  public static function rename($id, $name) {
    $folder = Folder::find($id);
    $folder->name = $name;
    $folder->save();
  }
}