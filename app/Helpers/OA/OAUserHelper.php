<?php namespace App\Helpers\OA;

class OAUserHelper
{
  public static function get($oaAuth, $oaUserId=null)
  {
    $url = \Config::get('oa')['apiUrl'] . '/t/users/'.(isset($oaUserId) ? $oaUserId : 'self');
    return OAHelper::get($url, $oaAuth);
  }
}