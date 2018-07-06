<?php namespace App\Helpers;

class OAHelper
{
  public static function checkOAToken($user)
  {
    $header = self::getCurlHeader($user);
    $valid = self::checkTokenValidity($header, $user);
    if (!$valid) {
      self::refreshToken($user);
      $header = self::getCurlHeader([
        'token_type'=>$user->oa_token_type,
        'access_token'=>$user->oa_access_token
      ]);
      $valid = self::checkTokenValidity($header, $user);
    }
    return $valid;
  }

  public static function getCurlHeader($oaAuth)
  {
    return [
      'Authorization: ' . $oaAuth['token_type'] . ' ' . $oaAuth['access_token'],
      'Content-Type: application/json',
      'Accept: application/json, text/plain, */*'
    ];
  }

  public static function checkTokenValidity($header, $user) {
    // fetch self
    $url = \Config::get('oa')['apiUrl'].'/t/users/self?' . $user->oa_last_team_id;
    try {
      $jsonStr = CurlHelper::get($url, $header);
    } catch (ErrorException $e) {
      $jsonStr = FALSE;
    }
    $result = false;
    if ($jsonStr === FALSE) {
    } else {
      $authResult = json_decode($jsonStr, true);
      if ($authResult['status']) {
        $result = true;
      }
//      else {
//        if ($authResult['code'] == 11000) {
//          $refreshResult = self::refreshToken(
//            $user->oa_token_type,
//            $user->oa_access_token,
//            $user->oa_refresh_token
//          );
//          if (isset($refreshResult)) {
//            $newTokenInfo = json_decode($jsonStr);
//            if ($newTokenInfo['status']) {
//              $user->oa_access_token = $newTokenInfo['result']['accessToken'];
//              $user->oa_token_type = $newTokenInfo['result']['tokenType'];
//              $user->oa_expires_in = $newTokenInfo['result']['expiresIn'];
//              $user->save();
//              $result = true;
//            }
//          }
//        }
//      }
    }
    return $result;
  }

  public static function refreshToken($user) {
    $tokenType = $user->oa_token_type;
    $accessToken = $user->oa_access_token;
    $refreshToken = $user->oa_refresh_token;

    $url = \Config::get('oa')['apiUrl'].'/t/auth/refresh';
    $header = [
      'Authorization: ' . $tokenType . ' ' . $accessToken,
      // 'Content-Type: application/json',
      'Accept: application/json, text/plain, */*'
    ];
    $postData = "refreshToken=" . $refreshToken;

    try {
      $jsonStr = CurlHelper::post($url, $postData, $header);
    } catch (ErrorException $e) {
      $jsonStr = FALSE;
    }
    $result = null;
    if ($jsonStr === FALSE) {
    } else {
      $newTokenInfo = json_decode($jsonStr, true);

      if ($newTokenInfo['status']) {
        $user->oa_access_token = $newTokenInfo['result']['accessToken'];
        $user->oa_token_type = $newTokenInfo['result']['tokenType'];
        $user->oa_expires_in = $newTokenInfo['result']['expiresIn'];
        $user->save();
        $result = [
          'oa_access_token' => $newTokenInfo['result']['accessToken'],
          'oa_token_type' => $newTokenInfo['result']['tokenType']
        ];
      }
    }
    return $result;
  }

  public static function get($urlSuffix, $oaAuth, $params=[]) {
    $url = \Config::get('oa')['apiUrl'].$urlSuffix;

    // parameters
    $keyValueArray = [];
    foreach( $params as $key=>$value ) {
      $keyValueArray[] = $key.'='.$value;
    }
    $dataStr = empty($params) ? '' : implode('&', $keyValueArray);
    $header = self::getCurlHeader($oaAuth);

    return CurlHelper::getData($url.'?'.$dataStr, $header);
  }

  public static function post($urlSuffix, $oaAuth, $params=[]) {
    $url = \Config::get('oa')['apiUrl'].$urlSuffix;

    // parameters
    $keyValueArray = [];
    foreach( $params as $key=>$value ) {
      $keyValueArray[] = $key.'='.$value;
    }
    $dataStr = empty($params) ? '' : implode('&', $keyValueArray);
    $header = self::getCurlHeader($oaAuth);

    /* params = [
    'username' => $data['email'],
    'password' => $data['password'],
    'teamId' => ''
    ]*/

    return CurlHelper::postData($url, $header, $dataStr);
  }
}