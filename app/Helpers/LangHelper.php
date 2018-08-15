<?php namespace App\Helpers;

use App;
use App\Models\Lang;

class LangHelper {

  public static function setLang($langCode) {
    // $langCode = [zh-hk|zh-cn|en-us]
    app()->setLocale(substr($langCode,-2));
  }

  public static function oaLangIdToAppLangId($oaLangId) {
    $result = Lang::whereDefault(1)->value('id');
    $lang = Lang::whereOaLangId($oaLangId)->first();
    if(isset($lang)) {
      $result = $lang->id;
    }
    return $result;
  }

}