<?php namespace App\Http\Controllers\ApiV2;

use App\User;
use App\Models\Folder;

use App\Helpers\FolderHelper;

class FolderController extends BaseController {

  public function show($id) {
    if($id==0) {
      return $this->getUser()->folder;
    }

    $folders = Folder::with('documents')->descendantsAndSelf($id)->totree();
    $folder = $folders[0];
    $folder->ancestors = FolderHelper::getAncestors($folder->id);
    foreach( $folder->children as $child) {
      $child->folderCount = $child->children->count();
      $child->documentCount = FolderHelper::getDocumentCount($child->id);
    }
    return $folder;
  }

  public function index() {
    $type = \Input::get('type','');
    $result = [];
    if(!empty($type)) {
      switch( $type) {
        case 'public':
          $publicFolder = Folder::whereName('public')->first();
          if (\Input::has('folderName')) {
            $folderId = $publicFolder->descendants()->whereName(\Input::get('folderName'))->value('id');
          }
          else {
            $folderId = $publicFolder->id;
          }
          return redirect('/apiv2/folders/'.$folderId);
          // $result = FolderHelper::getPublicFolder();
          break;
        case 'shared':
          break;
        case 'personal':
          $userFolder = $this->getUser()->folder;
          if (\Input::has('folderName')) {
            $folderId = $userFolder->descendants()->whereName(\Input::get('folderName'))->value('id');
          }
          else {
            $folderId = $userFolder->id;
          }
          return redirect('/apiv2/folders/'.$folderId);
          break;
        case 'all':
          $userId = \Input::get('user_id');
          $result = [
            'personalFolders'=>FolderHelper::getPersonalFolders($userId),
            'publicFolders'=>FolderHelper::getPublicFolders(),
            'sharedFolders'=>FolderHelper::getSharedFolders()
          ];
          break;
      }
    }
    return response()->json($result);
  }

  public function update($id)
  {
    if(\Input::has('command')) {
      return $this->processCommand($id);
    }
  }

  public function store()
  {
    if (\Input::has('command')) {
      return $this->processCommand();
    }
  }

  public function processCommand($id=0) {
    $command = \Input::get('command');
    switch($command) {
      case 'RENAME':
        $fileType = \Input::get('fileType');
        $fileItemId = \Input::get('fileItemId');
        $newName = \Input::get('newName');
        if($fileType == 'folder') {
          FolderHelper::rename($fileItemId, $newName);
        }
        else {
          DocumentHelper::rename($fileItemId, $newName);
        }
        break;
      case 'DROP':
        $targetFolderId = \Input::get('targetFolderId', 0);
        $fileType = \Input::get('fileType');
        $fileItemId = \Input::get('fileItemId');
        $documentIds = [];
        $folderIds = [];
        if($fileType == 'folder') {
          $folderIds = [$fileItemId];
        } else {
          $documentIds = [$fileItemId];
        }
        FolderHelper::moveItemsToFolder($targetFolderId, $documentIds, $folderIds);
        break;
      case 'NEW':
        $parentFolderId = \Input::get('parent_folder_id');
        $parentFolder = Folder::find($parentFolderId);
        $newFolder = FolderHelper::newFolder($parentFolder);
        return response()->json([
          'status'=>'ok'
        ]);
        break;
      case 'UPDATE_FOLDER_NAME':
        $folder = Folder::find($id);
        $folder->name = \Input::get('name');
        $folder->save();
        break;
      case 'DELETE':
        $folderIds = \Input::get('ids');
        for($i=0; $i<count($folderIds); $i++) {
          $this->deleteFolder($folderIds[$i]);
        }
        break;
      case 'MOVE_SELECTION':
      case 'MOVE_ITEM':
        $targetFolderId = \Input::get('targetFolderId',0);
        $documentIds = getIdArray(\Input::get('documentIds', ''));
        $folderIds = getIdArray(\Input::get('folderIds', ''));
        FolderHelper::moveItemsToFolder($targetFolderId, $documentIds, $folderIds);
//        $targetFolder = Folder::find($targetFolderId);
//        if(isset($targetFolder)) {
//          $documentIdsStr = \Input::get('documentIds', '');
//          if(!empty($documentIdsStr)) {
//            $documentIds = explode(',', $documentIdsStr);
//            DocumentHelper::moveDocumentsToFolder($documentIds, $targetFolder);
//          }
//          $folderIdsStr = \Input::get('folderIds', '');
//          if(!empty($folderIdsStr)) {
//            $folderIds = explode(',', $folderIdsStr);
//            FolderHelper::moveFoldersToFolder($folderIds, $targetFolder);
//          }
//        }
        break;
      case 'COPY_SELECTION':
      case 'COPY_ITEM':
        $targetFolderId = \Input::get('targetFolderId',0);
        $targetFolder = Folder::find($targetFolderId);
        if(isset($targetFolder)) {
          $documentIdsStr = \Input::get('documentIds', '');
          if(!empty($documentIdsStr)) {
            $documentIds = explode(',', $documentIdsStr);
            DocumentHelper::copyDocumentsToFolder($documentIds, $targetFolder);
          }
          $folderIdsStr = \Input::get('folderIds', '');
          if(!empty($folderIdsStr)) {
            $folderIds = explode(',', $folderIdsStr);
            FolderHelper::copyFoldersToFolder($folderIds, $targetFolder);
          }
        }
        break;
    }
    return response()->json([
      'status'=>'ok'
    ]);
  }

  public function deleteFolder($id) {
    $folder = Folder::find($id);
    $childFolders = Folder::descendantsAndSelf($id);
    foreach( $childFolders as $childFolder ) {
      foreach($childFolder->documents as $document ) {
        MediaHelper::deleteMedia($document->media_id);
      }
    }
    $folder->delete();
  }

  public function init() {
    $supervisor = User::whereName('supervisor')->first();

    $root = Folder::create(['name'=>'root', 'is_system'=>1, 'writable'=>0, 'owned_by'=>$supervisor->id]);
    $root->saveAsRoot();

    $public = Folder::create([
      'name'=>'public',
      'description'=>'Public Folder',
      'is_system'=>1,
      'writable'=>0,
      'owned_by'=>$supervisor->id]);
    $public->appendToNode($root);
    $public->save();

    $scan = Folder::create([
      'name'=>'scan',
      'description'=>'Scan',
      'is_system'=>1,
      'writable'=>1,
      'owned_by'=>$supervisor->id]);
    $scan->appendToNode($public);
    $scan->save();

    $meetings = Folder::create([
      'name'=>'meetings',
      'description'=>'Meetings',
      'is_system'=>1,
      'writable'=>0,
      'owned_by'=>$supervisor->id]);
    $meetings->appendToNode($public);
    $meetings->save();

    $shared = Folder::create([
      'name'=>'shared',
      'description'=>'Shared',
      'is_system'=>1,
      'writable'=>1,
      'owned_by'=>$supervisor->id]);
    $shared->appendToNode($public);
    $shared->save();

    $usersFolder = Folder::create(['name'=>'users', 'is_system'=>1, 'writable'=>0]);
    $usersFolder->appendToNode($root);
    $usersFolder->save();

    $users = User::all();
    foreach($users as $user) {
      $userFolder = Folder::create([
        'name'=>$user->name,
        'description'=>$user->full_name,
        'is_system'=>1,
        'writable'=>0,
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
    dd('ok');
  }

  public function destroy($id)
  {
    $this->deleteFolder($id);
    return response()->json([
      'status' => 'ok'
    ]);
  }

}