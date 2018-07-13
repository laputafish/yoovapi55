<?php namespace App\Helpers;

use App\Models\Lang;
use App\Models\IrdForm;

class FormTemplateHelper {
  public static function getTemplateFilePath( $form, $irdFormCode, $langCode = 'en-us' ) {
    $lang = Lang::whereCode($langCode)->first();
    $irdForm = IrdForm::whereFormCode($irdFormCode)->first();

    $irdFormFile = $irdForm->files()->whereLangId($lang->id)->value('file');
    return storage_path('forms/'.$irdFormFile);
  }
}