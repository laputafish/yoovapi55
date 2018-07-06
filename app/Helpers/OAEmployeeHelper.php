<?php namespace App\Helpers;

use App\Models\TeamJob;

use App\Events\TaxFormStatusUpdatedEvent;

class OAEmployeeHelper
{
  public static function get($employeeId, $oaAuth, $teamId) {
    $curlHeader = OAHelper::getCurlHeader($oaAuth);

    $url = \Config::get('oa')['apiUrl'].'/user/employees/'.$employeeId.'?teamId='.$teamId;
    return CurlHelper::get($url, $curlHeader);

  }
}