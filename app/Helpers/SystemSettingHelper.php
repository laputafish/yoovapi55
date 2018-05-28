<?php namespace App\Helpers;

use App\Models\Setting;

class SystemSettingHelper
{
  const INITIAL_SETTINGS = [
    'support_name' => 'Administrator',
    'support_email' => 'dominic@yoov.com',
    'dn_auto_confirm_period' => 5,
    'auto_confirm_dn' => 1,
    'test_mode' => 1,
    'email_notification_on_order_submission' => 1,
    'end_user_licence_agreement' => '',
    'email_notification_on_teacher_request_submission' => 0,
    'default_company_id' => 1,
    'image_sm_size_w' => 128,
    'image-sm_size_h' => 128,
    'image_xs_size_w' => 48,
    'image_xs_size_h' => 48,
    'office_theme' => 'blue',
    'center_theme' => 'yellow',
    'recruiter_theme' => 'green'
  ];

  public static function get($key, $default = '')
  {
    $result = $default;
    $setting = Setting::where('name', $key)->first();
    if (is_null($setting)) {
      $setting = new Setting();
      $setting->name = $key;
      $setting->value = $default;
      $setting->save();
    } else {
      $result = $setting->value;
    }
    return $result;
  }

  public static function set($key, $value)
  {
    $setting = Setting::where('name', $key)->first();
    if (is_null($setting)) {
      $setting = new Setting();
      $setting->name = $key;
    }
    $setting->value = $value;
    $setting->save();
  }

  public static function has($key)
  {
    $setting = Setting::where('name', $key)->first();
    $result = !is_null($setting);
    return $result;
  }

  public static function init()
  {
  }

  public static function upgrade()
  {
    $result = '';
    $keyNames = array_keys( self::INITIAL_SETTINGS );
    $existingKeys = Setting::lists('name')->toArray();

    $newKeyNames = array_diff( $keyNames, $existingKeys );
    foreach( $newKeyNames as $name ) {
      $setting = new Setting();
      $setting->name = $name;
      $setting->value = self::INITIAL_SETTINGS[$name];
      $setting->save();
      $result .= '<li>'.$setting->name.' => '.$setting->value.'</li>';
    }
    if(!empty($result)) {
      $result = '<h4>Newly added settings</h4>'.
        '<ul>'.
        $result.
        '</ul>';
    }

    $result .= '<h4>Current Settings</h4>';
    $result .= '<ul>';
    $allSettings = Setting::all();
    foreach( $allSettings as $setting ) {
      $result .= '<li>'.$setting->name.' => '.$setting->value.'</li>';
    }
    $result .= '</ul>';
    return $result;
  }
}
