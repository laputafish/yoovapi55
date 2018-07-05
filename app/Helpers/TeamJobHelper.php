<?php namespace App\Helpers;

use App\Models\TeamJobItem;

class TeamJobHelper {
  public static function getOrCreateItem($job, $employeeId) {
    $item = $job->items()->whereEmployeeId($employeeId)->first();
    if(is_null($item)) {
      $item = new TeamJobItem;
      $item->employee_id = $employeeId;

      $job->items()->save($item);
    }
    return $item;
  }
}