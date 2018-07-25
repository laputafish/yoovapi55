<?php namespace App\Helpers;

use App\Models\IrdForm;
use App\Models\Lang;
use App\Models\IrdFormFileField;

use App\Helpers\IrData;

class IrdFormHelper {
  protected static $defaults = [];
  protected static $defaultsx = [
    'areaCodeResAddr' => 'H',
    'martialStatus' => '2',
    'spouseName' => '(spouse name)',
    'spouseHkid' => 'C123456(7)',
    'spousePpNum' => 'PP12345678',
    'ptPrinEmp' => '(ptPrinEmp)',

    'placeOfResInd' => '1',

    'addrOfPlace1' => 'Rm 406, Peace Bldg., Peace St., HK',
    'natureOfPlace1' => 'Flat',
    'perOfPlace1' => '20160401-20170331',
    'rentPaidEr1' => 100000,
    'rentPaidEe1' => 20000,
    'rentRefund1' => 30000,
    'rentPaidErByEe1' => 10000,

    'addrOfPlace2' => 'Rm 306, Justice Bldg., 1 Justice Rd., HK',
    'natureOfPlace2' => 'Flat',
    'perOfPlace2' => '20160901-20170331',
    'rentPaidEr2' => 10000,
    'rentPaidEe2' => 154000,
    'rentRefund2' => 140000,
    'rentPaidErByEe2' => 20000,

    'overseaIncInd' => '1',
    'amtPaidOverseaCo' => 'US$40,000 (HK$312,000)',
    'nameOfOverseaCo' => 'Good Harvest (International) Co Ltd',
    'addrOfOverseaCo' => 'No. 8, 400th Street, New York, USA',
    'remarks' => 'Remarks'
  ];

  public static function generate($team, $employeeId, $formCode, $langCode, $options=[])
  {
    // Fetch related IRD Form Record
    $irdForm = IrdForm::whereFormCode(strtoupper($formCode))->first();

    // Set language for text translation in case
    $lang = Lang::whereCode($langCode)->first();
    LangHelper::setLang($lang->code);

    // Prepare output file path
    $outputFilePath = array_key_exists('outputFilePath', $options) ? $options['outputFilePath'] : null;
    $irdFormFile = $irdForm->files()->whereLangId( $lang->id )->first();
    $templateFilePath = storage_path('forms/'.$irdFormFile->file);

    // Prepare data
    $irDataClassPrefix = substr(strtolower($formCode),-2)== 'pc' ?
      substr($formCode,0,strlen($formCode)-2) :
      $formCode;
    $irDataHelperClassName = '\\App\\Helpers\\IrData\\'.camelize(strtolower($irDataClassPrefix.'Helper'));
    $options = array_merge($options, ['defaults'=>self::$defaults]);
    $data = $irDataHelperClassName::get($team, $employeeId, $options);

    // process
    $pdfOptions = [
      'title'=>$formCode,
      'topOffset'=>$irdFormFile->top_offset,
      'rightMargin'=>$irdFormFile->right_margin,
      'templateFilePath'=>$templateFilePath
    ];
    $pdf = new FormPdf($pdfOptions);
    $fieldList = $irdFormFile->fields;
    self::fillData($pdf, $fieldList, $data);

    // Output
    if(isset($outputFilePath)) {
      if (file_exists($outputFilePath)) {
        unlink($outputFilePath);
      }
      $pdf->Output($outputFilePath, 'F');
    } else {
      $pdf->Output('ird_'.$formCode.'.pdf');
    }
    return;
  }

  private static function fillData($pdf, $fieldList, $data) {
    foreach($fieldList as $item) {
      if($item->hidden) {
        continue;
      }
      $align = isset($item->align) ? $item->align : 'L';
      $lang = isset($item->lang) ? $item->lang : 'eng';
      $fontStyle = isset($item->font_style) ? $item->font_style : '';
      switch ($item->type) {
        case 'string':
          $text = $data->{$item->key};

          if($text == '0') {
            if ($item->blank_if_zero) {
              break;
            }
          } else if(empty($text)) {
            break;
          }

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