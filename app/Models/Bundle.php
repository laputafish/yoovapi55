<?php

namespace App\Models;

class Bundle extends Model
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
    'price_type'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function categories() {
    return $this->belongsToMany('App\Models\Category', 'bundle_categories', 'bundle_id', 'category_id')
      ->withPivot('qty');
  }


  public function products() {

  }
}
