<?php namespace App\Helpers\OA;

use App\User;

use App\Helpers\CurlHelper;

class OAHelper
{
  public static function checkOAToken($user)
  {
    $header = self::getCurlHeader($user);
    $valid = self::checkTokenValidity($header, $user);
    if (!$valid) {
      self::refreshToken($user);
      $header = self::getCurlHeader([
        'oa_token_type'=>$user->oa_token_type,
        'oa_access_token'=>$user->oa_access_token
      ]);
      $valid = self::checkTokenValidity($header, $user);
    }
    return $valid;
  }

  public static function getCurlHeader($oaAuth)
  {
    $result = [
      'Authorization: ' . $oaAuth['oa_token_type'] . ' ' . $oaAuth['oa_access_token'],
      'Content-Type: application/json',
      'Accept: application/json, text/plain, */*'
    ];
    return $result;
  }


  public static function checkTokenValidity($header, $user) {
    // fetch self
    $url = \Config::get('oa')['apiUrl'].'/t/users/self?' . $user->oa_last_team_id;
    $curlResult = FALSE;
    try {
      $jsonStr = CurlHelper::get($url, $header);
      $curlResult = json_decode($jsonStr, true);
    } catch (ErrorException $e) {
      $curlResult = FALSE;
    }

//    try {
//      $jsonStr = CurlHelper::get($url, $header);
//    } catch (ErrorException $e) {
//      $jsonStr = FALSE;
//    }
    $result = false;
    if ($curlResult === FALSE) {
    } else {
      $authResult = $curlResult;
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


  public static function refreshTokenByTeam($team) {
    OAHelper::updateTeamToken($team);
    return $team->getOaAuth();
  }

  public static function updateTeamToken($team)
  {
    $user = User::whereOaLastTeamId($team->oa_team_id)->first();
    self::refreshTeamToken($user, $team);
  }

  public static function refreshTeamToken($user, $team)
  {
    $tokenType = $user->oa_token_type;
    $accessToken = $user->oa_access_token;
    $refreshToken = $user->oa_refresh_token;
//echo 'tokenType = '.$tokenType; nf();
//echo 'accessToken = '.$accessToken; nf();
//echo 'refreshToken = '.$refreshToken; nf();
    if(isset($team)) {
      $newTokenInfo = self::doRefreshToken($tokenType, $accessToken, $refreshToken);
      $team->oa_access_token = $newTokenInfo['accessToken'];
      $team->oa_token_type = $newTokenInfo['tokenType'];
      $team->save();
    }
  }

  public static function doRefreshToken($tokenType, $accessToken, $refreshToken)
  {
    $url = \Config::get('oa')['apiUrl'] . '/t/auth/refresh';
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
      $response = json_decode($jsonStr, true);
      $result = $response['result'];
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
          'oa_token_type' => $newTokenInfo['result']['tokenType'],
          'oa_expires_in' => $newTokenInfo['result']['expiresIn']
        ];
      }
    }
    return $result;
  }

  public static function get($url, $oaAuth) {
    $curlHeader = OAHelper::getCurlHeader($oaAuth);
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
  public static function xxxget($urlSuffix, $oaAuth, $params=[]) {
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

  public static function xxxpost($urlSuffix, $oaAuth, $params=[]) {
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