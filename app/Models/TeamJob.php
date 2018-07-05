<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamJob extends Model
{
  protected $fillable = [
    'status',
    'fiscal_year',
    'job_type'
  ];



  public function team() {
    return $this->belongsTo('App\Models\Team', 'team_id');
  }

  public function items() {
    return $this->hasMany('App\Models\TeamJobItem', 'team_job_id');
  }

  public function getOrCreateItem($employeeId) {
    $item = $this->items()->whereEmployeeId($employeeId)->first();
    if(is_null($item)) {
      $item = new TeamJobItem;
      $item->employee_id = $employeeId;

      $this->items()->save($item);
    }
    return $item;
  }

}
