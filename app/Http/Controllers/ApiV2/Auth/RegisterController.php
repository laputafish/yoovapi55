<?php

namespace App\Http\Controllers\Apiv2\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiV2\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use \Mail;

class RegisterController extends BaseController
{
  /*
  |--------------------------------------------------------------------------
  | Register Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users as well as their
  | validation and creation. By default this controller uses a trait to
  | provide this functionality without requiring any additional code.
  |
  */

  use RegistersUsers;

  /**
   * Where to redirect users after registration.
   *
   * @var string
   */
  protected $redirectTo = '/apiv2/registered';

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest');
  }

  /**
   * Get a validator for an incoming registration request.
   *
   * @param  array  $data
   * @return \Illuminate\Contracts\Validation\Validator
   */
  protected function validator(array $data)
  {
    return Validator::make($data, [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:6|confirmed',
    ]);
  }

  /**
   * Create a new user instance after a valid registration.
   *
   * @param  array  $data
   * @return \App\User
   */
  protected function create(array $data)
  {
    $confirmationCode = str_random(30);

    $user = User::create([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => bcrypt($data['password']),
      'confirmation_code' => $confirmationCode
    ]);

    Mail::send('email.verify', compact('confirmationCode'), function($message) use ($data) {
      $message->to( $data['email'], $data['name'])
        ->subject('Verify your email address');
    });

    return $user;
  }

  protected function registered(Request $request, $user)
  {
    return response()->json([
      'status'=>'ok',
      'message'=>'Email verification required. Please check your email and activate your account.'
    ]);
    //
  }
}
