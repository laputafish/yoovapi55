<?php namespace App\Helpers\OA;

use App\Models\TeamJob;
use App\Helpers\CurlHelper;
use App\Events\TaxFormStatusUpdatedEvent;

class OAPayslipHelper
{
  public static function get($oaAuth, $employeeId, $teamId)
  {
    $url = \Config::get('oa')['apiUrl'].'/admin/payslips?'.
      'employeeId='.$employeeId.'&'.
      'teamId=' . $teamId.'&'.
      'status=completed';

    return OAHelper::get($url, $oaAuth);
  }
}