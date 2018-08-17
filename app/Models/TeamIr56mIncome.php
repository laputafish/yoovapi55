<?php

namespace App\Models;

class TeamIr56mIncome extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'team_id',
    'ir56m_income_id',
    'pay_type_ids'
  ];

  public $timestamps = false;

  public function ir56mIncome() {
    return $this->belongsTo('App\Models\Ir56mIncome');
  }
}