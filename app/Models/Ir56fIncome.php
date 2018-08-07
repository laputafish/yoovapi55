<?php

namespace App\Models;

class Ir56fIncome extends BaseModel
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'name_tag',
    'with_input',
    'input_label',
    'seq_no',
    'is_default',
    'description_tag'
  ];

  public $timestamps = false;

  public function teamIr56fIncomes() {
    return $this->hasMany('App\Models\TeamIr56fIncome');
  }
}