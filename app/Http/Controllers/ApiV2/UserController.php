<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\User;
use App\Models\UserInfo;

class UserController extends Controller
{
    public function init() {
        $users = [
            ['name'=>'yoovtest1', 'email'=>'yoovtest1@gmail.com', 'password'=>bcrypt('yoovyoov')],
            ['name'=>'yoovtest2', 'email'=>'yoovtest2@gmail.com', 'password'=>bcrypt('yoovyoov')],
            ['name'=>'yoovtest3', 'email'=>'yoovtest3@gmail.com', 'password'=>bcrypt('yoovyoov')],
            ['name'=>'yoovtest4', 'email'=>'yoovtest4@gmail.com', 'password'=>bcrypt('yoovyoov')],
            ['name'=>'yoovtest5', 'email'=>'yoovtest5@gmail.com', 'password'=>bcrypt('yoovyoov')],
            ['name'=>'yoovtest6', 'email'=>'yoovtest6@gmail.com', 'password'=>bcrypt('yoovyoov')],
            ['name'=>'yoovtest7', 'email'=>'yoovtest7@gmail.com', 'password'=>bcrypt('yoovyoov')],
        ];

        $userInfos = [];
        foreach( $users as $i=>$user ) {
            $dbUser = User::whereName( $user['name'] )->first();
            if(is_null($dbUser)) {
                $dbuser = User::create($user);
            }
            $userInfos[] = $this->createInfo( $dbUser );
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

    public function createInfo( $user ) {
        $info = UserInfo::create();

        $user->info()->save($info);
        return $info;
    }

    public function auth() {
        $input = \Input::all();
        $email = $input['email'];
        $result = null;
        $user = User::whereEmail($email)->first();
        if(isset($user)) {
            if (\Hash::check($input['password'], $user->password)) {
                $result = $user;
            }
        }
        return response()->json([
            'status'=>(isset($result) ? 'ok' : 'fails'),
            'user'=>$result
        ]);
    }

    public function getUser() {
//        $user = Auth::user();
        return response()->json(request()->user());
    }
}
