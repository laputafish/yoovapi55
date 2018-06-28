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
    $status = true;
    $oaAuth = [];
    $token = '';

    // Check is supervisor
    $user = User::whereEmail($email)->first();
    if (isset($user)) {
      $isSupervisor = $user->hasRole('supervisor');
    }

    if (!$isSupervisor) {
      $authResult = $this->loginOA($email, $password, $teamId);
//      return $authResult;

      if (empty($authResult)) {
        $status = false;
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
//    $options = array(
//      'http' => array(
//        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
//        'method' => 'POST',
//        'content' => http_build_query($data)
//      )
//    );
//    $context = stream_context_create($options);
    try {
      $jsonStr = $this->postData($url, $data);
//print_r( $jsonStr ); nl();
//      // $jsonStr = $this->get_contents($url, false, $context);
//      echo '1';
    } catch (ErrorException $e) {
//      echo '2';
      $jsonStr = FALSE;
    }
//    echo '3';
    $result = [];
    if ($jsonStr === FALSE) {
// dd('false');
//      return [];
//      return response()->json([
//        'status' => false,
//        'message' => 'Access Denied'
//      ]);
    } else {
// dd('ok');
      $authResult = json_decode($jsonStr, true);
      if ($authResult['status']) {
        $result = $authResult['result'];
      }
    }
    return $result;
  }

  public function postData( $url, $data ) {
//    $options = array(
//      'http' => array(
//        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
//        'method' => 'POST',
//        'content' => http_build_query($data)
//      )
//    );
//    $context = stream_context_create($options);
    $username=$data['email'];
    $password=$data['password'];
    $teamId="";

//    $url="http://www.myremotesite.com/index.php?page=login";
    $cookie="cookie.txt";

    $postdata = "email=".$username."&password=".$password."&teamId=".$teamId;

    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt ($ch, CURLOPT_REFERER, $url);

    curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt ($ch, CURLOPT_POST, 1);
    $result = curl_exec ($ch);

    curl_close($ch);
    return $result;
  }
  public function get_contentsx($url, $u = false, $c = null, $o = null)
  {
    echo 'url=['.$url.'] '; nl();
    $originalAgent = ini_get('user_agent');
    echo 'user agent = '.$originalAgent; nl(); nl();
    ini_set('user_agent', 'Mozilla/5.0');
    echo 'new user agent = '.ini_get('user_agent' ); nl();
    $headers = get_headers($url);
    ini_set('user_agent', $originalAgent);
    echo 'headers: '; nl();
    print_r( $headers ); nl(); nl();
    $status = substr($headers[0], 9, 3);
    echo 'status: '; nl();

    $a = file_get_contents($url, $u, $c, $o);
    print_r( $a ); nl();
    echo "***********************"; nl();

    echo $status;
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
