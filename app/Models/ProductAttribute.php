<?php

namespace App\Models;

class ProductAttribute extends Model
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'product_id',
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

  public function product() {
    return $this->belongsTo( 'App\Models\Product');
  }
}
