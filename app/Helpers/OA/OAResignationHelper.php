<?php namespace App\Helpers\OA;

use App\Models\TeamJob;
use App\Helpers\CurlHelper;
use App\Events\xxxTaxFormStatusUpdatedEvent;

class OAResignationHelper
{
  public static function get($oaAuth, $employeeId, $oaTeamId)
  {
    $url = \Config::get('oa')['apiUrl'] . '/user/resignation?employeeId='.$employeeId.'&teamId='.$oaTeamId;
    return OAHelper::get($url, $oaAuth);
  }
}
