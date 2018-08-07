<?php

namespace App\Models;

class TeamIr56fIncome extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'id',
    'team_id',
    'ir56f_income_id',
    'pay_type_ids'
  ];

  public $timestamps = false;

  public function ir56fIncome() {
    return $this->belongsTo('App\Models\Ir56fIncome');
  }
}