<?php namespace App\Helpers;

use App\Models\TeamJob;

use App\Events\TaxFormStatusUpdatedEvent;

class OAUserHelper
{
  public static function get($employeeId, $oaAuth) {
    $curlHeader = OAHelper::getCurlHeader($oaAuth);

    $url = \Config::get('oa')['apiUrl'].'/user/employees/'.$employeeId;
    $params = ''; /*
      include=groups,workingGroups,educations,nationality,payrollRules,experiences,emergencyContacts,paymentMethod,permissions,locations
      &
      teamId=fb319c5f-cfa1-4498-a31f-8ce925326b1e
    */
    if(!empty($params)) {
      $url = $url.'?'.$params;
    }

    return CurlHelper::get($url, $curlHeader);

  }
}