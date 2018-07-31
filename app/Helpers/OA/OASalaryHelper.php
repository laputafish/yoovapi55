<?php namespace App\Helpers\OA;

use App\Models\TeamJob;
use App\Helpers\CurlHelper;
use App\Events\xxxTaxFormStatusUpdatedEvent;

class OASalaryHelper
{
  public static function get($oaAuth, $employeeId, $teamId)
  {
    $url = \Config::get('oa')['apiUrl'] . '/admin/employees/' .
      $employeeId . '/salaries?teamId=' . $teamId;
    return OAHelper::get($url, $oaAuth);
  }
}