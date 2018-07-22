<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\OA\OAHelper;

class OATokenController extends BaseAuthController {
  protected $modelName = 'IrdForm';

  public function store() {
    $status = false;
    $result = [];
    $command = \Input::get('command', '');
    switch( $command ) {
      case 'refresh':
        $tokenInfo = OAHelper::refreshToken($this->user);
        $status = true;
        $result = [
          'oaAccessToken' => $tokenInfo['oa_access_token'],
          'oaTokenType' => $tokenInfo['oa_token_type'],
          'oaExpiresIn' => $tokenInfo['oa_expires_in']
        ];
        break;
    }
    return response()->json([
      'status'=>$status,
      'result'=>$result
    ]);
  }
}
