<?php namespace App\Helpers;

use App\Models\IrdForm;
use App\Models\Lang;
use App\Models\IrdFormFileField;

use App\Helpers\IrData;
use App\Helpers\OA\OAHelper;
use App\Helpers\OA\OATeamHelper;

class IrdFormHelper
{
  protected static $defaults = [];
//  protected static $defaultsx = [
//    'areaCodeResAddr' => 'H',
//    'maritalStatus' => '2',
//    'spouseName' => '(spouse name)',
//    'spouseHkid' => 'C123456(7)',
//    'spousePpNum' => 'PP12345678',
//    'ptPrinEmp' => '(ptPrinEmp)',
//
//    'placeOfResInd' => '1',
//
//    'addrOfPlace1' => 'Rm 406, Peace Bldg., Peace St., HK',
//    'natureOfPlace1' => 'Flat',
//    'perOfPlace1' => '20160401-20170331',
//    'rentPaidEr1' => 100000,
//    'rentPaidEe1' => 20000,
//    'rentRefund1' => 30000,
//    'rentPaidErByEe1' => 10000,
//
//    'addrOfPlace2' => 'Rm 306, Justice Bldg., 1 Justice Rd., HK',
//    'natureOfPlace2' => 'Flat',
//    'perOfPlace2' => '20160901-20170331',
//    'rentPaidEr2' => 10000,
//    'rentPaidEe2' => 154000,
//    'rentRefund2' => 140000,
//    'rentPaidErByEe2' => 20000,
//
//    'overseaIncInd' => '1',
//    'amtPaidOverseaCo' => 'US$40,000 (HK$312,000)',
//    'nameOfOverseaCo' => 'Good Harvest (International) Co Ltd',
//    'addrOfOverseaCo' => 'No. 8, 400th Street, New York, USA',
//    'remarks' => 'Remarks'
//  ];

  public static function getIrdFormData($team, $irdForm, $formEmployee, $options = [])
  {
    $isSample = array_key_exists('mode', $options) ? $options['mode'] == 'sample' : false;
    $irdDataHelperClassName = '\\App\\Helpers\\IrData\\' .
      camelize(strtolower($irdForm->ird_code)) .
          ($isSample ? 'Sample' : '') .
          'Helper';
    $irdEmployee = $irdDataHelperClassName::get($team, $formEmployee->employee_id, $options);
    return $irdEmployee;

  }

  public static function fetchDataAndGeneratePdf($outputFilePath, $team, $employeeId, $formCode, $irdInfo, $options = [])
  {
    // IRD Master Data
    $irdMaster = array_key_exists('irdMaster', $options) ? $options['irdMaster'] : [];
    $langCode = $irdInfo['langCode'];

    // Fetch related IRD Form Record
    $irdForm = $irdInfo['irdForm']; // IrdForm::whereFormCode(strtoupper($formCode))->first();
    // Set language for text translation in case
    $lang = Lang::whereCode($langCode)->first();
    LangHelper::setLang($lang->code);

    // Prepare output file path
//    $outputFilePath = array_key_exists('outputFilePath', $options) ? $options['outputFilePath'] : null;
    $irdFormFile = $irdForm->files()->whereLangId($lang->id)->first();

    $templateFilePath = storage_path('forms/' . $irdFormFile->file);

    // Prepare data
    $options = array_merge($options, ['defaults' => self::$defaults]);

    $irDataClassPrefix = substr(strtolower($formCode), -2) == 'pc' ?
      substr($formCode, 0, strlen($formCode) - 2) :
      $formCode;

    $irDataHelperClassName = '\\App\\Helpers\\IrData\\' . camelize(strtolower($irDataClassPrefix . 'Helper'));
    $irdEmployee = $irDataHelperClassName::get($team, $employeeId, $options);

//    echo 'irdEmployee'; nf();
    $pdfData = array_merge($irdMaster, $irdEmployee);

    // process
    $pdfOptions = [
      'title' => $formCode,
      'topOffset' => $irdFormFile->top_offset,
      'rightMargin' => $irdFormFile->right_margin,
      'templateFilePath' => $templateFilePath
    ];
    $pdf = new FormPdf($pdfOptions);
    $fieldList = $irdFormFile->fields->where('for_testing_only', 0);
//echo 'irdFormFile->id = '.$irdFormFile->id; nf();
//    $a = array_map(function($item) {
//      return $item['key'];
//    }, $fieldList->toArray());

    self::fillData($pdf, $fieldList, $pdfData);

    // Output
    if (isset($outputFilePath)) {
      if (file_exists($outputFilePath)) {
        unlink($outputFilePath);
      }
      $pdf->Output($outputFilePath, 'F');
    } else {
      $pdf->Output('ird_' . $formCode . '.pdf');
    }
//    $pdf->endPage();
    unset($pdf);
    return $irdEmployee;
  }

  public static function buildPdf($options)
  {
    // options = {
    //    title
    //    data
    //    fields
    //    templateFile
    //    outputFile,
    //
    //    topOffset,
    //    rightMargin
    // }
    $pdfOptions = [
      'title' => $options['title'],
      'topOffset' => $options['topOffset'],
      'rightMargin' => $options['rightMargin'],
      'templateFilePath' => $options['templateFile']
    ];
    $pdf = new FormPdf($pdfOptions);
    self::fillData($pdf, $options['fields'], $options['data']);

    // create folder if not exists
    $outputFile = $options['outputFile'];
    $folder = pathinfo($outputFile, PATHINFO_DIRNAME);
    FolderHelper::checkCreateFolders($folder);

    if (isset($outputFile)) {
      if (file_exists($outputFile)) {
        unlink($outputFile);
      }
      $pdf->Output($outputFile, 'F');
    } else {
      $pdf->Output('output.pdf');
    }
  }

  public static function fillData($pdf, $fieldList, $data)
  {
    foreach ($fieldList as $item) {
      if ($item->hidden) {
        continue;
      }
      if (!empty($item->show_if_key)) {
        if(
          (is_numeric($data[$item->show_if_key]) && $data[$item->show_if_key]==0) ||
          ($data[$item->show_if_key]=='0') ||
          ($data[$item->show_if_key]=='')
        ) {
          continue;
        }
      }
      $align = isset($item->align) ? $item->align : 'L';
      $lang = isset($item->lang) ? $item->lang : 'eng';
      $fontStyle = isset($item->font_style) ? $item->font_style : '';
      $text = $data[$item->key];
      switch ($item->type) {
        case 'markx':
          $lang = 'eng';
          if($text == '0' || (is_numeric($text) && ($text == 0))) {
            $text = '';
          } else {
            $text = 'X';
          }
          $x = $item->x;
          $y = $item->y;
          $width = $item->width;
          $fontSize = $item->font_size;

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
        case 'tick':
          $lang = 'symbol';
          if($text == '0' || (is_numeric($text) && ($text == 0))) {
            $text = '';
          } else {
            $text = '3';
          }
          $x = $item->x;
          $y = $item->y;
          $width = $item->width;
          $fontSize = $item->font_size;

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
        case 'string':
          if ($text == '0') {
            if ($item->blank_if_zero) {
              break;
            }
          } else if (empty($text)) {
            break;
          }

          // lang
          if (hasChinese($text)) {
            $lang = 'chn';
          }

          // init
          $x = $item->x;
          $y = $item->y;
          $width = $item->width;
          $fontSize = $item->font_size;
          $appendAsterisk = isset($item->append_asterisk) ? $item->append_asterisk : false;

          // Check is currency
          if (!empty($text)) {
            if ($item->to_currency) {
              $text = toCurrency(str_replace(',', '', $text));
            }
          }

          // Append Asterisk
          if ($appendAsterisk) {
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