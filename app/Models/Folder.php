<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Folder extends Model
{
  use NodeTrait;
  public $timestamp = false;

  protected $fillable = [
    'name',
    'description',
    'owned_by',
    'is_system',
    'remark'
  ];

  public function owner() {
    return $this->belongsTo('App\User', 'owned_by');
  }

  public function user() {
    return $this->belongsToMany('App\User', 'folder_users', 'folder_id', 'user_id')
      ->withPivot('writable');
  }

  public function documents() {
    return $this->hasMany('App\Models\Document', 'folder_id');
  }
}
