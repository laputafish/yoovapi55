<?php namespace App\Helpers;

use App\Models\TeamJob;

use App\Events\TaxFormStatusUpdatedEvent;

class OAEmployeeHelper
{
  public static function get($employeeId, $oaAuth, $teamId) {
    $curlHeader = OAHelper::getCurlHeader($oaAuth);

    $url = \Config::get('oa')['apiUrl'].'/user/employees/'.$employeeId.'?teamId='.$teamId;
    $jsonStr = CurlHelper::get($url, $curlHeader);
    $curlResult = json_decode($jsonStr, true);

    if($curlResult === FALSE) {
      $result = [
        'code'=>0,
        'message'=>'Cannot connect to OA server.'
      ];
    } else {
      if($curlResult['status']) {
        $result = $curlResult['result'];
      } else {
        $result = [
          'code' => $curlResult['code'],
          'message' => $curlResult['message']
        ];
      }
    }
    return $result;
  }
}