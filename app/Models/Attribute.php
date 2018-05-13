<?php

namespace App\Models;

class Attribute extends Model
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
    'remark',
    'label',
    'type'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function options() {
    return $this->hasMany( 'App\Models\AttributeOption');
  }
}
