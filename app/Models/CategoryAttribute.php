<?php

namespace App\Models;

class CategoryAttribute extends Model
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'category_id',
    'attribute_id',
    'price',
    'remark'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function category() {
    return $this->belongsTo( 'App\Models\Category');
  }
}
