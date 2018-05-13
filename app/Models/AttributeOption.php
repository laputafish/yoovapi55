<?php

namespace App\Models;

class AttributeOption extends Model
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'attribute_id',
    'label',
    'price',
    'remark'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function attribute() {
    return $this->belongsTo( 'App\Modles\Attribute' );
  }
}
