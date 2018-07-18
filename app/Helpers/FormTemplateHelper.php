<?php namespace App\Helpers;

use App\Models\Lang;
use App\Models\IrdForm;

class FormTemplateHelper {
  public static function getTemplateFilePath( $irdForm, $langId = null) {
    if(is_null($langId)) {
      $lang = Lang::whereCode('en-us')->first();
      $langId = $lang->id;
    }
    $irdFormFile = $irdForm->files()->whereLangId($langId)->value('file');
    return storage_path('forms/'.$irdFormFile);
  }
}