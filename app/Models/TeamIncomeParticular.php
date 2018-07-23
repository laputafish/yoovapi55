<?php

namespace App\Models;

class TeamIncomeParticular extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'team_id',
    'income_particular_id',
    'pay_type_ids'
  ];

  public $timestamps = false;

  public function incomeParticular() {
    return $this->belongsTo('App\Models\IncomeParticular');
  }
//  public function payTypes() {
//    return $this->hasMany('App\Models\TeamIncomeParticularPayType');
//  }
}