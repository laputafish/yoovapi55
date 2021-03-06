<?php

namespace App\Models;

class TeamIr56bIncome extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'team_id',
    'ir56b_income_id',
    'pay_type_ids'
  ];

  public $timestamps = false;

  public function ir56bIncome() {
    return $this->belongsTo('App\Models\Ir56bIncome');
  }
}