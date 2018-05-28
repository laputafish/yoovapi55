<?php
namespace App\Helpers;

use App\Models\Folder;

class FolderHelper {
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