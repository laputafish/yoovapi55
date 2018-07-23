<?php

namespace App\Models;

class xxxTeamIncomeParticularPayType extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'pay_type_id',
    'team_income_particular_id'
  ];
  public $timestamps = false;

}