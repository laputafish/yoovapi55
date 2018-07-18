<?php namespace App\Helpers\OA;

class OATeamHelper {
  public static function get($oaAuth, $oaTeamId) {
    $url = \Config::get('oa')['apiUrl'].'/t/teams/'. $oaTeamId;
//    echo 'url = '.$url; nl();
//echo 'oa_team_id = '.$oaTeamId; nl();
//dd($oaAuth);
    return OAHelper::get($url, $oaAuth);
//    $jsonStr = CurlHelper::get($url, $curlHeader);
//    $curlResult = json_decode($jsonStr, true);
//
//    if($curlResult === FALSE) {
//      $result = [
//        'code'=>0,
//        'message'=>'Cannot connect to OA server.'
//      ];
//    } else {
//      if($curlResult['status']) {
//        $result = $curlResult['result'];
//      } else {
//        $result = [
//          'code' => $curlResult['code'],
//          'message' => $curlResult['message']
//        ];
//      }
//    }
//    return $result;
  }
}