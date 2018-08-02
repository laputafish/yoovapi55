<?php namespace App\Helpers;

// use MpfPdf;
use setasign\Fpdi\TcpdfFpdi;
use setasign\Fpdi\Fpdi;
//use Elibyy\TCPDF\TCPDF;
//use Elibyy\TCPDF\FpdiTCPDFHelper;

class MpfHelper {
  public static $yOffset = 2;

  public static function generate() {
    $data = [
      'title' => 'MPF 2018',
      'company' => [
        'business_name' => 'Yoov Internet Technology Co. Ltd.',
        'file_no' => '6Y1-12345678',
        'sheet_no' => 1,
        'designation' => 'Director',
        'form_date' => '2017-04-24'
      ],
      'employee' => [
        'surname' => 'TIN',
        'given_name' => 'BIU YI',
        'full_name_in_chinese' => '田表易',
        'hkid' => 'E123456(7)',
        'passport_no' => 'xxxx',
        'place_of_issue' => 'xxxxx',
        'gender' => 'M',
        'marital_status' => 2, /* 1=Single/Widowed/Divorced/Living Apart, 2=Married */
        'spouse_name' => 'TSANG HING SUNG',
        'spouse_hkid' => 'E246801(2)',
        'spouse_passport_no' => '',
        'spouse_place_of_issue' => '',
        'residential_address' => 'Flat 306, Justice Bldg., 1 Justice Road, HK',
        'postal_address' => 'Flat 307, Justice Bldg., 1 Justice Road, HK',
        'capacity_employed' => 'Sales Manager (Asia Pacific)',
        'part_time_principal_employer' => 'Hong Kong Kowloon New Territories',
        'employment_period_start' => '2016-04-01',
        'employment_period_end' => '2017-03-31'
      ],
      'income_particulars' => [
        'salary' => ['start_date'=>'2016-04-01','end_date'=>'2017-03-31','amount'=>611200],
        'leave_pay' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'director_fee' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'commission' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'bonus' => ['start_date'=>'2016-04-01', 'end_date'=>'2017-03-31', 'amount'=>100000],
        'back_pay' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'payment_from_retirement_scheme' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'salaries_tax_paid_by_employer' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'education_benefits' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'gain_realized_under_share_option_scheme' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'any_other_rewards' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0],
        'pensions' => ['start_date'=>'', 'end_date'=>'', 'amount'=>0]
      ],
      'residential_place_provided' => [
        [
          'address'=>'Rm 406, Peace Bldg., 8 Peace St., HK',
          'nature'=>'Flat',
          'start_date'=>'2016-04-01',
          'end_date'=>'2016-08-31',
          'rent_paid_to_landlord_by_employer'=>100000,
          'rent_paid_to_landlord_by_employee'=>0,
          'rent_refunded_to_employee_by_employer'=>0,
          'rent_refunded_to_employer_by_employee'=>10000
        ],
        [
          'address'=>'Rm 306, Justice Bldg., 1 Justice Rd., HK',
          'nature'=>'Flat',
          'start_date'=>'2016-09-01',
          'end_date'=>'2017-03-31',
          'rent_paid_to_landlord_by_employer'=>0,
          'rent_paid_to_landlord_by_employee'=>154000,
          'rent_refunded_to_employee_by_employer'=>140000,
          'rent_refunded_to_employer_by_employee'=>0
        ]
      ],
      'payment_by_non_hong_kong_company'=>[
        'wholly_or_partly'=>true,
        'non_hong_kong_company_name'=>'Good Harvest (International) Co Ltd.',
        'address' => 'No. 8, 400th Street, New York, USA',
        'amount_hkd' => '312000',
        'amount_other_currency' => 'US$40,000'
      ],
      'remark'=>'Remark'
    ];

    $formFile = storage_path('forms/m_ir56b_17_18e.pdf');

//    $pdf = new Fpdi();
//    $pdfHelper = new FpdiTCPDFHelper();
//    $pdf = new TCPDF($pdfHelper);
    $pdf = new FormPdf();
    $pdf->AddPage();
    $pdf->setSourceFile( $formFile );
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId);
//  $pdf->setPrintHeader(false);
//  $pdf->setPrintFooter(false);

    // File properties
    $pdf->setTitle( $data['title'] );
    $pdf->setSubject('MPF');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configuration
    $pdf->SetFont('msungstdlight', '', 12);

    //Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

    //****************
    // Company Name
    //****************
    $pdf->outputText(52,44.5 + self::$yOffset,
      12, // font size
      86, // width
      $data['company']['business_name'],
      'L');

    //****************
    // File No.
    //****************
    $fileNoSegs = explode('-', $data['company']['file_no']);
    // File No. - part 1
    $pdf->outputText(142,34+ self::$yOffset,
      12, // font size
      18, // width
      $fileNoSegs[0],
      'C');
    // File no. - part 2
    $pdf->outputText(162,34+ self::$yOffset,
      12, // font size
      36, // width
      $fileNoSegs[1],
      'C');

    //****************
    // Sheet No.
    //****************
    $pdf->outputChars( 168.5, 44.5 + self::$yOffset,
      12,
      30,
      6,
      $data['company']['sheet_no'],
      'R');

    //*********************
    // Employee - Surname
    //*********************
    $pdf->outputChars( 86.8, 56.5+ self::$yOffset,
      10,
      112,
      20,
      $data['employee']['surname'],
      'L');

    //***********************
    // Employee - Given name
    //***********************
    $pdf->outputText( 86.8, 61+ self::$yOffset,
      10,
      112,
      $data['employee']['given_name'],
      'L');

    //*********************************
    // Employee - Full name in chinese
    //*********************************
    $pdf->outputText( 86.8, 65 + self::$yOffset,
      10,
      112,
      $data['employee']['full_name_in_chinese'],
      'L',
      'chn');

    //*********************************
    // Employee - HKID
    //*********************************
    $hkid = preg_replace('/[\(\)\-]/', '', $data['employee']['hkid']);
    $hkidSeg0 = substr($hkid,0,1);
    $hkidSeg1 = substr($hkid,1,6);
    $hkidSeg2 = substr($hkid,-1);

    $pdf->outputChars( 144.5, 70.5 + self::$yOffset,
      12,
      10,
      2,
      $hkidSeg0,
      'R');

    $pdf->outputChars( 158.5, 70.5 + self::$yOffset,
      12,
      30,
      6,
      $hkidSeg1,
      'L');

    $pdf->outputChars( 191.5, 70.5 + self::$yOffset,
      12,
      5,
      1,
      $hkidSeg2,
      'L');

    //********************************************
    // Employee - Passport no. and place of issue
    //********************************************
    $pdf->outputTwoFields( 130, 75.8+ self::$yOffset,
      12,
      60,
      $data['employee']['passport_no'],
      $data['employee']['place_of_issue'],
      'L');

    //********************************************
    // Employee - Gender
    //********************************************
    $pdf->outputChars( 191.8, 79.8+ self::$yOffset,
      12,
      7,
      1,
      strtoupper($data['employee']['gender']),
      'L');

    //********************************************
    // Employee - Marital Status
    //********************************************
    $pdf->outputChars( 191.8, 85+ self::$yOffset,
      12,
      7,
      1,
      $data['employee']['marital_status'],
      'L');

    //********************************************
    // Employee - Spouse name
    //********************************************
    $pdf->outputText( 67, 90 + self::$yOffset,
      12,
      0,
      $data['employee']['spouse_name'],
      'L');

    //********************************************
    // Employee - Spouse "HKID" or "passport no. and place of issue"
    //********************************************
    if(!empty($data['employee']['spouse_hkid'])) {
      $pdf->outputText(140, 94 + self::$yOffset,
        12,
        0,
        $data['employee']['spouse_hkid'],
        'L');
    } else {
      $pdf->outputTwoFields( 140, 94 + self::$yOffset,
        12,
        0,
        'passport no.  ',//$data['employee']['spouse_passport_no'],
        'hong kong', // $data['employee']['spouse_place_of_issue'],
        'L');
    }

    //********************************************
    // Employee - Residential Address
    //********************************************
    $pdf->outputText( 47, 99 + self::$yOffset,
      12,
      0,
      $data['employee']['residential_address'],
      'L');

    //********************************************
    // Employee - Postal Address
    //********************************************
    if($data['employee']['postal_address'] != $data['employee']['residential_address'] &&
      !empty(trim($data['employee']['postal_address']))) {
      $pdf->outputText( 82, 103.5 + self::$yOffset,
        12,
        0,
        $data['employee']['postal_address'],
        'L');
    }

    //********************************************
    // Employee - Capacity employed
    //********************************************
    $pdf->outputText( 64, 108+ self::$yOffset,
      12,
      0,
      $data['employee']['capacity_employed'],
      'L');

    //********************************************
    // Employee - Part-time Principal employer
    //********************************************
    $pdf->outputText( 109, 112.8 + self::$yOffset,
      12,
      0,
      $data['employee']['part_time_principal_employer'],
      'L');

    //********************************************
    // Employee - Employment period
    //********************************************
    $dmy = date('dmY',
      strtotime($data['employee']['employment_period_start'])
    );

    $pdf->outputChars( 136.3, 117.7+ self::$yOffset,
      10,
      28,
      8,
      $dmy,
      'L');

    $dmy = date('dmY',
      strtotime($data['employee']['employment_period_end'])
    );

    $pdf->outputChars( 170.3, 117.7+ self::$yOffset,
      10,
      28,
      8,
      $dmy,
      'L');

    //*******************************
    // Employee - Income particulars
    //*******************************
    $y = 140.5;

    $heights = [
      'salary' => 3.9,
      'leave_pay' => 3.8,
      'director_fee' => 3.7,
      'commission' => 3.5,
      'bonus' => 6.8,
      'back_pay' => 4.2,
      'payment_from_retirement_scheme' => 3.8,
      'salaries_tax_paid_by_employer' => 3.8,
      'education_benefits' => 3.8,
      'gain_realized_under_share_option_scheme' => 6.2,
      'any_other_rewards' => 4.6,
      'pensions' => 4.0
    ];

    $total = 0;
    foreach($data['income_particulars'] as $i=>$item) {
      $startDate = $item['start_date'];
      $endDate = $item['end_date'];
      $amount = $item['amount'];

      if(true || $amount > 0) {
        // start date
        $segs = getDMYSegs($startDate);
        $pdf->outputNumbers(94.5, $y+ self::$yOffset,
          10,
          27,
          3,
          $segs,
          'C');

        // end date
        $segs = getDMYSegs($endDate);
        $pdf->outputNumbers(125.5, $y+ self::$yOffset,
          10,
          27,
          3,
          $segs,
          'C');

        // amount
        $amount = floor($amount);
        $pdf->outputChars(153, $y+ self::$yOffset,
          10,
          37,
          9,
          $amount,
          'R');

        $total += $amount;
      }
      $y += $heights[$i];
    }

    // Total income
    $pdf->outputChars( 153, $y+ self::$yOffset,
      10,
      37,
      9,
      $total,
      'R');

    // Residential Place Provided
    $residentialProvided = count($data['residential_place_provided']) > 0;
    $pdf->outputText( 192, 199+ self::$yOffset,
      12,
      7,
      ($residentialProvided ? '1' : '0'),
      'C');

    if($residentialProvided) {
      $y = 224.7;
      $h = 5.1;
      foreach( $data['residential_place_provided'] as $item ) {
        $fields = [
          [$item['address'],10,55,'L'],
          [$item['nature'],10,20,'C'],
          [$item['start_date'],7,13,'C'],
          [$item['end_date'],7,13,'C'],
          [toCurrency($item['rent_paid_to_landlord_by_employer']),10,19,'R'],
          [toCurrency($item['rent_paid_to_landlord_by_employee']),10,19,'R'],
          [toCurrency($item['rent_refunded_to_employee_by_employer']),10,21,'R'],
          [toCurrency($item['rent_refunded_to_employer_by_employee']),10,19,'R']
        ];
        $x = 19;
        foreach($fields as $j=>$field) {
          $pdf->outputText($x, $y+ self::$yOffset, $field[1], $field[2],$field[0],$field[3],'eng','B');
          $x += $field[2];
        }
        $y += $h;
      }
    }

    //********************************************
    // Paid by non-Hong Kong company
    //********************************************
    $paidByNonHongKong = $data['payment_by_non_hong_kong_company']['wholly_or_partly'];
    $pdf->outputText( 192, 241.6+ self::$yOffset,
      12,
      7,
      ($paidByNonHongKong ? '1' : '0'),
      'C');
    if($paidByNonHongKong) {
      $pdf->outputText( 74, 245.5+ self::$yOffset,
        12,
        0,
        $data['payment_by_non_hong_kong_company']['non_hong_kong_company_name'],
        'L');
      $pdf->outputText( 32, 250+ self::$yOffset,
        12,
        0,
        $data['payment_by_non_hong_kong_company']['address'],
        'L');
      $pdf->outputText( 130, 254.5 + self::$yOffset,
        12,
        0,
        implode(',',[
          $data['payment_by_non_hong_kong_company']['amount_other_currency'],
          '(HK$'.$data['payment_by_non_hong_kong_company']['amount_hkd'].')'
        ]),
        'L');
    }

    $pdf->outputText( 54, 258.5 + self::$yOffset,
      12,
      0,
      $data['remark'],
      'L');

//    $pageId = $pdf->importPage(1);
//
//    $pdf->addPage();
//    $pdf->useImportedPage($pageId);

//    $pdf->Output;
//    $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
//    $pdf->SetFont('freeserif', '', 12 );
//    $pdf->Write(5, 'Hello World');

//
//    PDF::SetTitle('Hello World');
//    PDF::AddPage();
//    PDF::Write(0, 'Hello World');
//    PDF::Text(90, 140, 'TCPDF Demo');
//    PDF::Output('hello_world.pdf');
//    PDF::setHeaderCallback(function($pdf) {
//      dd('setHeaderCallback');
//    });
//    $pdf->SetHeaderCallback(function($pdf) {
//
//    });
//    $pdf->SetFooterCallback(function($pdf) {
//
//    });
    // $outputFile = storage_path('output/test.pdf');
    return $pdf->Output();
    // return $pdf->Output($outputFile, 'I');
  }


}