<?php namespace App\Http\Controllers\ApiV2\Auth;

use Illuminate\Routing\Controller as BaseController;
use App\User;

class OAAuthController extends BaseController
{
  public function login()
  {
    $email = \Input::get('email');
    $password = \Input::get('password');
    $teamId = \Input::get('teamId');
    $authorized = \Input::get('authorized', false);
    $isSupervisor = false;
    $status = 'ok';
    $oaAuth = [];
    $token = '';

    // Check is supervisor
    $user = User::whereEmail($email)->first();
    if (isset($user)) {
      $isSupervisor = $user->hasRole('supervisor');
    }

    if (!$isSupervisor) {
      $authResult = $this->loginOA($email, $password, $teamId);
      if (empty($authResult)) {
        $status = 'fails';
      } else {
        $oaAuth = $authResult;
        if (!isset($user)) {
          $emailSegs = explode('@', $email);
          $name = $emailSegs[0];
          $user = User::create([
            'name' => $name,
            'alias' => $name,
            'first_name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
          ]);
        }
        $user->oa_access_token = $authResult['accessToken'];
        $user->oa_expires_in = $authResult['expiresIn'];
        $user->oa_refresh_token = $authResult['refreshToken'];
        $user->oa_token_type = $authResult['tokenType'];
        $user->oa_updated_at = date('Y-m-d H:n:s');
        $user->save();

        $token = $user->createToken('*')->accessToken;
      }
    } else {
      $token = $user->createToken('*')->accessToken;
    }

    return response()->json([
      'status' => $status,
      'isSupervisor' => $isSupervisor,
      'token' => $token,
      'oaAuth' => $oaAuth
    ]);
//
//    else {
//      return response()->json([
//        'status'=>'ok',
//        'isSupervisor'=>
//      ])
//
//      ]
//    }
//
//    $data = [
//      'email' => $email,
//      'password' => $password,
//      'teamId' => \Input::get('teamId')
//    ];
//    $options = array(
//      'http' => array(
//        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
//        'method' => 'POST',
//        'content' => http_build_query($data)
//      )
//    );
//    $context = stream_context_create($options);
//    $jsonStr = file_get_contents($url, false, $context);
//
//    if ($jsonStr === FALSE) {
//      return response()->json([
//        'status' => false,
//        'message' => 'Access Denied'
//      ]);
//    } else {
//      $authResult = json_decode($jsonStr, true);
//      $result = $authResult['result'];
//      if($authResult['status']) {
//        $user = User::whereEmail($email)->first();
//        if(!isset($user)) {
//          $emailSegs = explode('@', $email);
//          $name = $emailSegs[0];
//          $user = User::create([
//            'name'=>$name,
//            'alias'=>$name,
//            'first_name'=>$name,
//            'email'=>$email,
//            'password'=>bcrypt($password)
//          ]);
//        }
//        $user->oa_access_token = $result['accessToken'];
//        $user->oa_expires_in = $result['expiresIn'];
//        $user->oa_refresh_token = $result['refreshToken'];
//        $user->oa_token_type = $result['tokenType'];
//        $user->oa_updated_at = date('Y-m-d H:n:s');
//        $user->save();
//
//        $isSupervisor = $user->hasRole('supervisor');
//
//        $token = $user->createToken('*')->accessToken;
//        return response()->json([
//          'status'=>'ok',
//          'token'=>$token,
//          'isSupervisor'=>$isSupervisor,
//          'oaAuth'=>$result
//        ]);
//      }
//      else {
//        return response()->json([
//          'status'=>'fails',
//          'message'=>'Access Denied'
//        ]);
//      }
//    }
  }

  public function loginOA($email, $password, $teamId)
  {
    $url = 'https://hr.yoov.com/api/v1/t/auth/login';
    $data = [
      'email' => $email,
      'password' => $password,
      'teamId' => $teamId
    ];
    $options = array(
      'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
      )
    );
    $context = stream_context_create($options);
    try {
      $jsonStr = $this->get_contents($url, false, $context);
    } catch (ErrorException $e) {
      $jsonStr = FALSE;
    }

    $result = [];
    if ($jsonStr === FALSE) {
//      return [];
//      return response()->json([
//        'status' => false,
//        'message' => 'Access Denied'
//      ]);
    } else {
      $authResult = json_decode($jsonStr, true);
      if ($authResult['status']) {
        $result = $authResult['result'];
      }
    }
    return $result;
  }

  public function get_contents($url, $u = false, $c = null, $o = null)
  {
    $headers = get_headers($url);
    $status = substr($headers[0], 9, 3);
    if ($status == '200') {
      return file_get_contents($url, $u, $c, $o);
    }
    return false;
  }

  public function loginxx()
  {
    $url = 'https://hr.yoov.com/api/v1/t/auth/login';
    $data = [
      'email' => \Input::get('email'),
      'password' => \Input::get('password'),
      'teamId' => \Input::get('teamId')
    ];
    $options = array(
      'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return response()->json([
      'result' => $result
    ]);

//
//    if ($result === FALSE) { /* Handle error */
//      return response()->json([
//        'status'=>false
//      ]);
//    } else {
//      $data = json_decode($result, true);
//      return response()->json($result);
//    }
  }
}
