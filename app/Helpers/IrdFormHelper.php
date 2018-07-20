<?php namespace App\Helpers;

use App\Models\IrdForm;
use App\Models\Lang;
use App\Models\IrdFormFileField;

use App\Helpers\IrData;

class IrdFormHelper {
  public static function generate($team, $employeeId, $formCode, $langCode, $options=[])
  {
    $form = null;
    $filePath = null;
    if(array_key_exists('form', $options)) { $form = $options['form']; }
    if(array_key_exists('filePath', $options)) { $form = $options['filePath']; }

    $irdForm = IrdForm::whereFormCode(strtoupper($formCode))->first();
    $lang = Lang::whereCode($langCode)->first();
    $irdFormFile = $irdForm->files()->whereLangId( $lang->id )->first();
    $templateFilePath = storage_path('forms/'.$irdFormFile->file);

    $irDataClassPrefix = substr(strtolower($formCode),-2)== 'pc' ?
      substr($formCode,0,strlen($formCode)-2) :
      $formCode;
    $irDataHelperClassName = '\\App\\Helpers\\IrData\\'.camelize(strtolower($irDataClassPrefix.'Helper'));

    // prepare data
    $data = $irDataHelperClassName::get($team, $employeeId, $form, $options);

    // process
    $options = [
      'title'=>$formCode,
      'topOffset'=>$irdFormFile->top_offset,
      'rightMargin'=>$irdFormFile->right_margin,
      'templateFilePath'=>$templateFilePath
    ];
    $pdf = new FormPdf($options);

    $fieldList = $irdFormFile->fields;
    self::fillData($pdf, $fieldList, $data);
    if(isset($finalFilePath)) {
      if (file_exists($finalFilePath)) {
        unlink($finalFilePath);
      }
      $pdf->Output($finalFilePath, 'F');
    } else {
      $pdf->Output('commencement_form.pdf');
    }
    return;
  }

  private static function fillData($pdf, $fieldList, $data) {
    foreach($fieldList as $item) {
      $align = isset($item->align) ? $item->align : 'L';
      $lang = isset($item->lang) ? $item->lang : 'eng';
      $fontStyle = isset($item->font_style) ? $item->font_style : '';
      switch ($item->type) {
        case 'string':
          $text = $data->{$item->key};

          // lang
          if(hasChinese($text)) {
            $lang = 'chn';
          }

          // init
          $x = $item->x;
          $y = $item->y;
          $width = $item->width;
          $fontSize = $item->font_size;
          $appendAsterisk = isset($item->append_asterisk) ? $item->append_asterisk : false;

          // Check is currency
          if(!empty($text)) {
            if($item->to_currency) {
              $text = toCurrency(str_replace(',', '', $text));
            }
          }

          // Append Asterisk
          if($appendAsterisk) {
            $x = 100;
            $width = 0;
            $align = 'R';
            $fontStyle = 'B';
            $text .= ' ****';
          }

          // Output
          $pdf->outputText(
            $x,
            $y,
            $fontSize,
            $width,
            $text,
            $align,
            $lang,
            null,
            $fontStyle
          );
          break;
        case 'char':
          break;
        case 'segments':
          break;

      }
    }

  }
}