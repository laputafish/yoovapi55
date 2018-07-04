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
    'income_particular_id'
  ];

  public $timestamps = false;

  public function payTypes() {
    return $this->hasMany('App\Models\TeamIncomeParticularPayType');
  }
}