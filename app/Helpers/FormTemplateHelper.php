<?php namespace App\Helpers;

use App\Models\Lang;
use App\Models\IrdForm;

class FormTemplateHelper {
  public static function getTemplateFilePath( $form, $irdFormCode, $langId = null) {
    if(is_null($langId)) {
      $lang = Lang::whereCode('en-us')->first();
      $langId = $lang->id;
    }
    $irdForm = IrdForm::whereFormCode($irdFormCode)->first();

    $irdFormFile = $irdForm->files()->whereLangId($langId)->value('file');
    return storage_path('forms/'.$irdFormFile);
  }
}