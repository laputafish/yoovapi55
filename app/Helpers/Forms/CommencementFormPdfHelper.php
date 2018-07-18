<?php namespace App\Helpers\Forms;

use setasign\Fpdi\TcpdfFpdi;
use setasign\Fpdi\Fpdi;

use App\Helpers\FormPdf;

use App\Models\IrdFormFileField;

class CommencementFormPdfHelper {
  public static function generate($data, $finalFilePath, $irdFormFile, $mappings=[] ) {
    $templateFilePath = storage_path('forms/'.$irdFormFile->file);

    $pdf = new FormPdf($irdFormFile->y_offset);
    $pdf->AddPage();
    $pdf->setSourceFile( $templateFilePath );
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId);

    // File properties
    $pdf->setTitle( $data->title );
    $pdf->setSubject('MPF');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configuration
    // $pdf->SetFont('msungstdlight', '', 12);

    $fieldList = $irdFormFile->fields;
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
            $width = 90;
            $align = 'R';
            $fontSize = 12;
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
    if(isset($finalFilePath)) {
      if (file_exists($finalFilePath)) {
        unlink($finalFilePath);
      }
      $pdf->Output($finalFilePath, 'F');
    } else {
      $pdf->Output('commencement_form.pdf');
    }
    return;





    //****************
    // Company Name
    //****************
//    $pdf->outputText(52,44.5 + self::$yOffset,
//      12, // font size
//      86, // width
//      $data['company']['business_name'],
//      'L');
//
//    //****************
//    // File No.
//    //****************
//    $fileNoSegs = explode('-', $data['company']['file_no']);
//    // File No. - part 1
//    $pdf->outputText(142,34+ self::$yOffset,
//      12, // font size
//      18, // width
//      $fileNoSegs[0],
//      'C');
//    // File no. - part 2
//    $pdf->outputText(162,34+ self::$yOffset,
//      12, // font size
//      36, // width
//      $fileNoSegs[1],
//      'C');
//
//    //****************
//    // Sheet No.
//    //****************
//    $pdf->outputChars( 168.5, 44.5 + self::$yOffset,
//      12,
//      30,
//      6,
//      $data['company']['sheet_no'],
//      'R');
//
//    //*********************
//    // Employee - Surname
//    //*********************
//    $pdf->outputChars( 86.8, 56.5+ self::$yOffset,
//      10,
//      112,
//      20,
//      $data['employee']['surname'],
//      'L');
//
//    //***********************
//    // Employee - Given name
//    //***********************
//    $pdf->outputText( 86.8, 61+ self::$yOffset,
//      10,
//      112,
//      $data['employee']['given_name'],
//      'L');
//
//    //*********************************
//    // Employee - Full name in chinese
//    //*********************************
//    $pdf->outputText( 86.8, 65 + self::$yOffset,
//      10,
//      112,
//      $data['employee']['full_name_in_chinese'],
//      'L',
//      'chn');
//
//    //*********************************
//    // Employee - HKID
//    //*********************************
//    $hkid = preg_replace('/[\(\)\-]/', '', $data['employee']['hkid']);
//    $hkidSeg0 = substr($hkid,0,1);
//    $hkidSeg1 = substr($hkid,1,6);
//    $hkidSeg2 = substr($hkid,-1);
//
//    $pdf->outputChars( 144.5, 70.5 + self::$yOffset,
//      12,
//      10,
//      2,
//      $hkidSeg0,
//      'R');
//
//    $pdf->outputChars( 158.5, 70.5 + self::$yOffset,
//      12,
//      30,
//      6,
//      $hkidSeg1,
//      'L');
//
//    $pdf->outputChars( 191.5, 70.5 + self::$yOffset,
//      12,
//      5,
//      1,
//      $hkidSeg2,
//      'L');
//
//    //********************************************
//    // Employee - Passport no. and place of issue
//    //********************************************
//    $pdf->outputTwoFields( 130, 75.8+ self::$yOffset,
//      12,
//      60,
//      $data['employee']['passport_no'],
//      $data['employee']['place_of_issue'],
//      'L');
//
//    //********************************************
//    // Employee - Gender
//    //********************************************
//    $pdf->outputChars( 191.8, 79.8+ self::$yOffset,
//      12,
//      7,
//      1,
//      strtoupper($data['employee']['gender']),
//      'L');
//
//    //********************************************
//    // Employee - Martial Status
//    //********************************************
//    $pdf->outputChars( 191.8, 85+ self::$yOffset,
//      12,
//      7,
//      1,
//      $data['employee']['marital_status'],
//      'L');
//
//    //********************************************
//    // Employee - Spouse name
//    //********************************************
//    $pdf->outputText( 67, 90 + self::$yOffset,
//      12,
//      0,
//      $data['employee']['spouse_name'],
//      'L');
//
//    //********************************************
//    // Employee - Spouse "HKID" or "passport no. and place of issue"
//    //********************************************
//    if(!empty($data['employee']['spouse_hkid'])) {
//      $pdf->outputText(140, 94 + self::$yOffset,
//        12,
//        0,
//        $data['employee']['spouse_hkid'],
//        'L');
//    } else {
//      $pdf->outputTwoFields( 140, 94 + self::$yOffset,
//        12,
//        0,
//        'passport no.  ',//$data['employee']['spouse_passport_no'],
//        'hong kong', // $data['employee']['spouse_place_of_issue'],
//        'L');
//    }
//
//    //********************************************
//    // Employee - Residential Address
//    //********************************************
//    $pdf->outputText( 47, 99 + self::$yOffset,
//      12,
//      0,
//      $data['employee']['residential_address'],
//      'L');
//
//    //********************************************
//    // Employee - Postal Address
//    //********************************************
//    if($data['employee']['postal_address'] != $data['employee']['residential_address'] &&
//      !empty(trim($data['employee']['postal_address']))) {
//      $pdf->outputText( 82, 103.5 + self::$yOffset,
//        12,
//        0,
//        $data['employee']['postal_address'],
//        'L');
//    }
//
//    //********************************************
//    // Employee - Capacity employed
//    //********************************************
//    $pdf->outputText( 64, 108+ self::$yOffset,
//      12,
//      0,
//      $data['employee']['capacity_employed'],
//      'L');
//
//    //********************************************
//    // Employee - Part-time Principal employer
//    //********************************************
//    $pdf->outputText( 109, 112.8 + self::$yOffset,
//      12,
//      0,
//      $data['employee']['part_time_principal_employer'],
//      'L');
//
//    //********************************************
//    // Employee - Employment period
//    //********************************************
//    $dmy = date('dmY',
//      strtotime($data['employee']['employment_period_start'])
//    );
//
//    $pdf->outputChars( 136.3, 117.7+ self::$yOffset,
//      10,
//      28,
//      8,
//      $dmy,
//      'L');
//
//    $dmy = date('dmY',
//      strtotime($data['employee']['employment_period_end'])
//    );
//
//    $pdf->outputChars( 170.3, 117.7+ self::$yOffset,
//      10,
//      28,
//      8,
//      $dmy,
//      'L');
//
//    //*******************************
//    // Employee - Income particulars
//    //*******************************
//    $y = 140.5;
//
//    $heights = [
//      'salary' => 3.9,
//      'leave_pay' => 3.8,
//      'director_fee' => 3.7,
//      'commission' => 3.5,
//      'bonus' => 6.8,
//      'back_pay' => 4.2,
//      'payment_from_retirement_scheme' => 3.8,
//      'salaries_tax_paid_by_employer' => 3.8,
//      'education_benefits' => 3.8,
//      'gain_realized_under_share_option_scheme' => 6.2,
//      'any_other_rewards' => 4.6,
//      'pensions' => 4.0
//    ];
//
//    $total = 0;
//    foreach($data['income_particulars'] as $i=>$item) {
//      $startDate = $item['start_date'];
//      $endDate = $item['end_date'];
//      $amount = $item['amount'];
//
//      if(true || $amount > 0) {
//        // start date
//        $segs = getDMYSegs($startDate);
//        $pdf->outputNumbers(94.5, $y+ self::$yOffset,
//          10,
//          27,
//          3,
//          $segs,
//          'C');
//
//        // end date
//        $segs = getDMYSegs($endDate);
//        $pdf->outputNumbers(125.5, $y+ self::$yOffset,
//          10,
//          27,
//          3,
//          $segs,
//          'C');
//
//        // amount
//        $amount = floor($amount);
//        $pdf->outputChars(153, $y+ self::$yOffset,
//          10,
//          37,
//          9,
//          $amount,
//          'R');
//
//        $total += $amount;
//      }
//      $y += $heights[$i];
//    }
//
//    // Total income
//    $pdf->outputChars( 153, $y+ self::$yOffset,
//      10,
//      37,
//      9,
//      $total,
//      'R');
//
//    // Residential Place Provided
//    $residentialProvided = count($data['residential_place_provided']) > 0;
//    $pdf->outputText( 192, 199+ self::$yOffset,
//      12,
//      7,
//      ($residentialProvided ? '1' : '0'),
//      'C');
//
//    if($residentialProvided) {
//      $y = 224.7;
//      $h = 5.1;
//
//      $INDEX_FONT_SIZE = 1;
//      $INDEX_WIDTH = 2;
//      $INDEX_TITLE = 0;
//      $INDEX_ALIGN = 3;
//      foreach( $data['residential_place_provided'] as $item ) {
//        $fields = [
//          [$item['address'],10,55,'L'],
//          [$item['nature'],10,20,'C'],
//          [$item['start_date'],7,13,'C'],
//          [$item['end_date'],7,13,'C'],
//          [toCurrency($item['rent_paid_to_landlord_by_employer']),10,19,'R'],
//          [toCurrency($item['rent_paid_to_landlord_by_employee']),10,19,'R'],
//          [toCurrency($item['rent_refunded_to_employee_by_employer']),10,21,'R'],
//          [toCurrency($item['rent_refunded_to_employer_by_employee']),10,19,'R']
//        ];
//        $x = 19;
//        foreach($fields as $j=>$field) {
//          $pdf->outputText($x, $y+ self::$yOffset,
//            $field[$INDEX_FONT_SIZE],
//            $field[$INDEX_WIDTH],
//            $field[$INDEX_TITLE],
//            $field[$INDEX_ALIGN],
//            'eng',
//            'B');
//          $x += $field[2];
//        }
//        $y += $h;
//      }
//    }
//
//    //********************************************
//    // Paid by non-Hong Kong company
//    //********************************************
//    $paidByNonHongKong = $data['payment_by_non_hong_kong_company']['wholly_or_partly'];
//    $pdf->outputText( 192, 241.6+ self::$yOffset,
//      12,
//      7,
//      ($paidByNonHongKong ? '1' : '0'),
//      'C');
//    if($paidByNonHongKong) {
//      $pdf->outputText( 74, 245.5+ self::$yOffset,
//        12,
//        0,
//        $data['payment_by_non_hong_kong_company']['non_hong_kong_company_name'],
//        'L');
//      $pdf->outputText( 32, 250+ self::$yOffset,
//        12,
//        0,
//        $data['payment_by_non_hong_kong_company']['address'],
//        'L');
//      $pdf->outputText( 130, 254.5 + self::$yOffset,
//        12,
//        0,
//        implode(',',[
//          $data['payment_by_non_hong_kong_company']['amount_other_currency'],
//          '(HK$'.$data['payment_by_non_hong_kong_company']['amount_hkd'].')'
//        ]),
//        'L');
//    }
//
//    $pdf->outputText( 54, 258.5 + self::$yOffset,
//      12,
//      0,
//      $data['remark'],
//      'L');
//
//    if(file_exists($finalFilePath)) {
//      unlink($finalFilePath);
//    }
//    $pdf->Output($finalFilePath, 'F');
  }
}