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
    if(FolderHelper::underPublic($folder)) {
      $ancestors = FolderHelper::getPublicAncestors($id);
//      $ancestors[] = [
//        'name'=>$folder->name,
//        'description'=>'Public '.$folder->description,
//        'id'=>$folder->id
//      ];
    }
    else {
      $ancestors = FolderHelper::getUserAncestors($id);
    }

    $folder->ancestors = $ancestors;
    foreach( $folders[0]->children as $child) {
      $child->folderCount = $child->children->count();
      $child->documentCount = FolderHelper::getDocumentCount($child->id);
    }
    return $folders[0];
  }

  public function index() {
    $type = \Input::get('type','');
    $result = [];
    if(!empty($type)) {
      switch( $type) {
        case 'public':
          $result = FolderHelper::getPublicFolders();
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
      case 'MOVE':
        $targetFolderId = \Input::get('targetFolderId',0);
        $targetFolder = Folder::find($targetFolderId);
        if(isset($targetFolder)) {
          $documentIdsStr = \Input::get('documentIds', '');
          if(!empty($documentIdsStr)) {
            $documentIds = explode(',', $documentIdsStr);
            DocumentHelper::moveDocumentsToFolder($documentIds, $targetFolder);
          }
          $folderIdsStr = \Input::get('folderIds', '');
          if(!empty($folderIdsStr)) {
            $folderIds = explode(',', $folderIdsStr);
            FolderHelper::moveFoldersToFolder($folderIds, $targetFolder);
          }
        }
        break;
      case 'COPY':
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
            FolderHelper::moveFoldersToFolder($folderIds, $targetFolder);
          }
        }
        break;
    }
    return response()->json([
      'status'=>'ok'
    ]);
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
//    echo 'destroy'."<Br/>\n";
    $folders = Folder::descendantsAndSelf($id);
//    dd( $folders->toArray() );
    foreach( $folders as $folder ) {
//      echo 'folder name = '.$folder->name."\n";
//      echo '   document count = '.$folder->documents()->count()."\n";
      foreach($folder->documents as $document ) {
//        echo 'document->name = '.$document->filename."<br/>\n";
        MediaHelper::deleteMedia($document->media_id);
        $document->delete();
      }
      $folder->delete();
    }
    return response()->json([
      'status' => 'ok'
    ]);
  }

}