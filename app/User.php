<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Helpers\FolderHelper;
use App\Models\UserInfo;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'alias',
      'first_name',
      'last_name',
      'mobile',
      'email',
      'password',
    ];

    protected $appends = [
      'folder'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function info() {
        return $this->hasOne('App\Models\UserInfo');
    }

    public function sharedFolders() {
      return $this->belongsToMany('App\Models\Folder', 'folder_users',
        'user_id', 'folder_id')
        ->pivot('writable');
    }

    public function getFullNameAttribute() {
      if(is_null($this->info)) {
        return $this->name;
      } else {
        $names = [];
        if(!empty($this->info->first_name)) {
          $names[] = $this->info->first_name;
        }
        if(!empty($this->info->last_name)) {
          $names[] = $this->info->last_name;
        }
        return implode(' ', $names);
      }
    }

    public function getFolderAttribute() {
      if(!isset($this->info)) {
        $info = UserInfo::create([]);
        $this->info()->save($info);
        $folder = FolderHelper::createUserFolder($this);
        $info->folder_id = $folder->id;
        $info->save();
      }
      else {
        $folder = $this->info->folder;
        if(is_null($folder)) {
          $folder = FolderHelper::createUserFolder($this);
          $this->info->folder_id = $folder->id;
          $this->info->save();
        }
      }
      FolderHelper::checkCreate('scan', 'Scan', $folder);
      return $folder;
    }

    public function getScanFolderAttribute() {
      $folders = $this->folders;
      $result = null;
      foreach( $folders as $folder ) {
        if($folder->name == 'scan') {
          $result = $folder;
          break;
        }
      }
      return $result;
    }
}
