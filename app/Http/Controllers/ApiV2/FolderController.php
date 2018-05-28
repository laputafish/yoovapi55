<?php namespace App\Http\Controllers\ApiV2;

use App\User;
use App\Models\Folder;

class FolderController extends BaseController {

  public function show($id) {
    $rows = Folder::descendantsAndSelf($id);
    return $rows;
  }

  public function index() {
    $type = \Input::get('type','');
    $result = [];
    if(!empty($type)) {
      switch( $type) {
        case 'public':
          $publicFolder = Folder::whereName('public')->first();
          $result = $publicFolder->descendants()->get();
      }
    }

    return response()->json($result);
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
}