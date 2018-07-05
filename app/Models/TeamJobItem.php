<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamJobItem extends Model
{
  public $incrementing = false;
  public $timestamps = false;

  protected $fillable = [
    'team_job_id',
    'employee_id'
  ];

  public function job() {
    return $this->belongsTo('App\Models\TeamJob', 'team_job_id' );
  }
}
