<?php namespace App\Http\Controllers\ApiV2;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\User;
use App\Models\UserInfo;

class UserController extends BaseController
{
  public function init2()
  {
    $users = [
      ['name' => 'yoovtest1', 'email' => 'yoovtest1@gmail.com', 'password' => bcrypt('yoovyoov')],
      ['name' => 'yoovtest2', 'email' => 'yoovtest2@gmail.com', 'password' => bcrypt('yoovyoov')],
      ['name' => 'yoovtest3', 'email' => 'yoovtest3@gmail.com', 'password' => bcrypt('yoovyoov')],
      ['name' => 'yoovtest4', 'email' => 'yoovtest4@gmail.com', 'password' => bcrypt('yoovyoov')],
      ['name' => 'yoovtest5', 'email' => 'yoovtest5@gmail.com', 'password' => bcrypt('yoovyoov')],
      ['name' => 'yoovtest6', 'email' => 'yoovtest6@gmail.com', 'password' => bcrypt('yoovyoov')],
      ['name' => 'yoovtest7', 'email' => 'yoovtest7@gmail.com', 'password' => bcrypt('yoovyoov')],
    ];

    $userInfos = [];
    foreach ($users as $i => $user) {
      $dbUser = User::whereName($user['name'])->first();
      if (is_null($dbUser)) {
        $dbuser = User::create($user);
      }
      $userInfos[] = $this->createInfo($dbUser);
    }

    // 0:yoovtest1
    //  - 1:yoovtest2
    //      - 3:yoovtest4
    //      - 4:yoovtest5
    //
    //  - 2:yoovtest3
    //      - 5:yoovtest6
    //      - 6:yoovtest7
    //

//    $headUser = User::whereName('yoovtest1')->first();
//  $headUserInfo = $this->createInfo( $headUser );

    $userInfos[0]->appendNode($userInfos[1]);
    $userInfos[0]->appendNode($userInfos[2]);

    $userInfos[1]->appendNode($userInfos[3]);
    $userInfos[1]->appendNode($userInfos[4]);

    $userInfos[2]->appendNode($userInfos[5]);
    $userInfos[2]->appendNode($userInfos[6]);

    return 'finished';
  }

  public function init()
  {
    define('NAME', 0);
    define('ALIAS', 1);
    define('LAST_NAME', 2);
    define('FIRST_NAME', 3);
    define('EMAIL', 4);
    define('MOBILE', 5);

    $users = [
      ['phil', 'Phil', 'Wong', '', 'philipwong@yoov.com', '61136557'],
      ['mark', 'Mark', 'Wong', '', 'hc.devmark@gmail.com', '64883360'],
      ['dominic', 'Dominic', 'Lee', '', 'dominic@yoov.com', '90279335'],
      ['peter', 'Peter', 'Leung', '', 'peterleung@yoov.com', '51260545'],
      ['raymond', 'Raymond', 'Cheung', '', 'raymondcheung@yoov.com', '51168205'],
      ['sugar', 'Sugar', 'Tsang', '', 'sugartsang@yoov.com', '67067103'],
      ['teresa', 'Teresa', 'Chan', '', 'teresa@utimeapps.com', ''],
      ['yvonne', 'Yvonne', 'Fan', '', 'yvonnefan@yoov.com', '64359588'],
      ['tommy', 'Tommy', 'Chan', '', 'tommychan@yoov.com', '61549843'],
      ['winnie', 'Winnie', 'Tse', 'Nga-lai', 'winnietse@yoov.com', ''],
      ['york', 'York', 'Liang', '', 'yorkliang@yoov.com', '60413927'],
      ['donald', 'Donald', 'Chow', '', 'donald@utimeapps.com', '93378630'],
      ['eric', 'Eric', 'Shek', '', 'ericshek@yoov.com', '65016603'],
      ['jack', 'jack', 'Wong', '', 'jackwong@yoov.com', '51098796'],
      ['jacob', 'Jacob', '', '', 'jacob@utimeapps.com', '66922577'],
      ['jennifer', 'Jennifer', 'Wong', '', 'jennifer@utimeapps.com', '60482678'],
      ['jessica', 'Jessica', 'Tam', 'Ka-yee', 'jessicatam@yoov.com', '91376708'],
      ['kelly', 'Kelly', 'Tsui', '', 'kellytsui@yoov.com', '98611397'],
      ['luke', 'Luke', 'Ng', '', 'lukeng@yoov.com', '62210324'],
      ['andriy', 'Andriy', 'Chiu', 'Kwok-wah', 'andriychiu@yoov.com', '63372725'],
      ['cyrus', 'Cyrus', 'Chow', '', 'cyrus@utimeapps.com', '91743087'],
      ['daniel', 'Daniel', 'Leung', '', 'danielleung@yoov.com', '92305065']
    ];
    foreach ($users as $user) {
      $row = User::whereEmail($user[EMAIL])->first();
      if (isset($row)) {
        $row->name = $user[NAME];
        $row->alias = $user[ALIAS];
        $row->first_name = $user[FIRST_NAME];
        $row->last_name = $user[LAST_NAME];
        $row->mobile = $user[MOBILE];
        $row->password = bcrypt($user[MOBILE]);
        $row->save();
      }
    }
    return 'ok';
  }

  public function createInfo($user)
  {
    $info = UserInfo::create();

    $user->info()->save($info);
    return $info;
  }

  public function auth()
  {
    $input = \Input::all();
    $email = $input['email'];
    $result = null;
    $user = User::whereEmail($email)->first();
    if (isset($user)) {
      if (\Hash::check($input['password'], $user->password)) {
        $result = $user;
      }
    }
    return response()->json([
      'status' => (isset($result) ? 'ok' : 'fails'),
      'user' => $result
    ]);
  }

  public function getUser()
  {
//        $user = Auth::user();
    return response()->json(request()->user());
  }
}
