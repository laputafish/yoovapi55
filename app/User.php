<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Helpers\FolderHelper;
use App\Models\UserInfo;
use App\Models\Role;

class User extends Authenticatable
{
  use HasApiTokens, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'employee_id',
    'name',
    'alias',
    'first_name',
    'last_name',
    'display_name',
    'mobile',
    'email',
    'password',
    'lang_id',
    'oa_token_type',
    'oa_access_token',
    'oa_refresh_token',
    'oa_expires_in',
    'oa_last_team_id'
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

  public function info()
  {
    return $this->hasOne('App\Models\UserInfo');
  }

  public function sharedFolders()
  {
    return $this->belongsToMany('App\Models\Folder', 'folder_users',
      'user_id', 'folder_id')
      ->pivot('writable');
  }

  public function getFullNameAttribute()
  {
    if (isset($this->display_name)) {
      return $this->display_name;
    } else if (is_null($this->info)) {
      return $this->name;
    } else {
      $names = [];
      if (!empty($this->info->first_name)) {
        $names[] = $this->info->first_name;
      }
      if (!empty($this->info->last_name)) {
        $names[] = $this->info->last_name;
      }
      return implode(' ', $names);
    }
  }

  public function getFolderAttribute()
  {
    if (!isset($this->info)) {
      $info = UserInfo::create([]);
      $this->info()->save($info);
      $folder = FolderHelper::createUserFolder($this);
      $info->folder_id = $folder->id;
      $info->save();
    } else {
      $folder = $this->info->folder;
      if (is_null($folder)) {
        $folder = FolderHelper::createUserFolder($this);
        $this->info->folder_id = $folder->id;
        $this->info->save();
      }
    }
    FolderHelper::checkCreate('scan', 'Scan', $folder);
    return $folder;
  }

  public function getScanFolderAttribute()
  {
    $folders = $this->folder->children;
    $result = null;
    foreach ($folders as $folder) {
      if ($folder->name == 'scan') {
        $result = $folder;
        break;
      }
    }
    return $result;
  }

  public function occupiedEquipments()
  {
    return $this->hasMany('App\Models\Equipment', 'occupied_by');
  }

  public function roles()
  {
    return $this->belongsToMany(Role::class);
  }

  public function authorizeRoles($roles)
  {
    if (is_array($roles)) {
      return $this->hasAnyRole($roles) ||
        abort(401, 'This action is unauthorized.');
    }
    return $this->hasRole($roles) ||
      abort(401, 'This action is unauthorized.');
  }

  public function hasAnyRole($roles)
  {
    return null !== $this->roles()->whereIn('name', $roles)->first();
  }

  public function hasRole($role)
  {
    return null !== $this->roles()->where('name', $role)->first();
  }

//  public function oaAuth() {
//    return $this->hasOne('App\Models\OAAuth');
//  }
  
  public function fillOAAuth($oaAuth) {
    $this->oa_access_token = $oaAuth['accessToken'];
    $this->oa_expires_in = $oaAuth['expiresIn'];
    $this->oa_refresh_token = $oaAuth['refreshToken'];
    $this->oa_token_type = $oaAuth['tokenType'];
    $this->oa_updated_at = date('Y-m-d H:n:s');
    $this->save();
  }

  public function taxForms() {
    return $this->hasMany('App\Models\TaxForm', 'employee_id', 'employee_id');
  }
}
