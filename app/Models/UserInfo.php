<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class UserInfo extends Model
{
  use NodeTrait;
    //

  public $timestamps = false;

  public $fillable = [
    'user_id'
  ];

  public function user() {
    return $this->belongsTo('App\Models\User');
  }
}
