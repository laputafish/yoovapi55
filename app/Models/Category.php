<?php

namespace App\Models;

class Category extends Model
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'alias',
    'price',
    'remark'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function attributes() {
    return $this->hasMany('App\Models\Attribute');
  }
}
