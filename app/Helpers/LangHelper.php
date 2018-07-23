<?php namespace App\Helpers;

use App;

class LangHelper {

  public static function setLang($langCode) {
    // $langCode = [zh-hk|zh-cn|en-us]
    app()->setLocale(substr($langCode,-2));
  }

}