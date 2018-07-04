<?php

namespace App\Models;

class IncomeParticular extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'with_input',
    'input_label'
  ];

  public function teamIncomeParticulars() {
    return $this->hasMany('App\Models\TeamIncomeParticular');
  }
}