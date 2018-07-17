<?php namespace App\Helpers\OA;

use App\Models\TeamJob;
use App\Helpers\CurlHelper;
use App\Events\TaxFormStatusUpdatedEvent;

class OASalaryHelper
{
  public static function get($employeeId, $oaAuth, $teamId)
  {
    $url = \Config::get('oa')['apiUrl'] . '/admin/employees/' .
      $employeeId . '/salaries?teamId=' . $teamId;
    return OAHelper::get($url, $oaAuth);
  }
}