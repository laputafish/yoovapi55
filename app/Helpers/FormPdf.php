<?php namespace App\Helpers;

use setasign\Fpdi;

class FormPdf extends Fpdi\TcpdfFpdi
{
  protected $tplId;
  protected $fontName = 'times';
  protected $fontNameChn = 'msungstdlight';

  public function __construct() {
    parent::__construct();
  }

  function Header() {}
  function Footer() {}

  function outputText($x, $y, $fontSize, $width, $text, $align='L', $lang='eng',$valign='M',$fontStyle='') {
    $fontName = $lang == 'eng' ? $this->fontName : $this->fontNameChn;

    if($width == 0) {
      $width = 200 - $x;
    }

    $this->setY($y);
    $this->setX($x);
    $this->SetFont($fontName, $fontStyle, $fontSize);
    $this->Cell($width, $h=0,
      $text, // $txt='',
      $border=0,
      $ln=0,
      $align,
      $fill=0,
      $link='',
      $stretch=0,
      $ignore_min_height=false,
      $calign='C',
      $valign='M');
  }

  function outputChars($x, $y, $fontSize, $width, $charCount, $chars, $align='L', $lang='eng') {
    $fontName = $lang == 'eng' ? $this->fontName : $this->fontNameChn;

    $this->setY($y);
    $this->setX($x);
    $this->SetFont($fontName, '', $fontSize);
    $unitWidth = $width / $charCount;

    // if width = 0, extend to end of line
    if($width == 0) {
      $width = 200 - $x;
    }

    $length = strlen($chars);
    if($align == 'R') {
      $this->setX($x + $unitWidth * ($charCount - $length));
    }
    for($i=0; $i<$charCount; $i++) {
      $this->Cell($unitWidth, $h = 0,
        substr($chars,$i,1), // $txt='',
        $border = 0,
        $ln = 0,
        'C',
        $fill = 0,
        $link = '',
        $stretch = 0,
        $ignore_min_height = false,
        $calign = 'C',
        $valign = 'M');
    }
  }

  function outputNumbers($x, $y, $fontSize, $width, $numberCount, $numbers, $align='L', $lang='eng') {
    $fontName = $lang == 'eng' ? $this->fontName : $this->fontNameChn;

    $this->setY($y);
    $this->setX($x);
    $this->SetFont($fontName, '', $fontSize);
    $unitWidth = $width / $numberCount;

    // if width = 0, extend to end of line
    if($width == 0) {
      $width = 200 - $x;
    }

    $length = count($numbers);
    if($align == 'R') {
      $this->setX($x + $unitWidth * ($numberCount - $length));
    }
    for($i=0; $i<$numberCount; $i++) {
      $this->Cell($unitWidth, $h = 0,
        $numbers[$i],
        $border = 0,
        $ln = 0,
        'C',
        $fill = 0,
        $link = '',
        $stretch = 0,
        $ignore_min_height = false,
        $calign = 'C',
        $valign = 'M');
    }
  }

  function xxxxxxoutputDigits($x, $y, $fontSize, $width, $digitCount, $number, $align='L', $lang='eng') {
    $fontName = $lang == 'eng' ? $this->fontName : $this->fontNameChn;

    $this->setY($y);
    $this->setX($x);
    $this->SetFont($fontName, '', $fontSize);
    $unitWidth = $width / $digitCount;

    $length = strlen($number);
    $this->setX($x + $unitWidth*($digitCount-$length));
    for($i=0; $i<$digitCount; $i++) {
      $this->Cell($unitWidth, $h = 0,
        substr($number,$i,1), // $txt='',
        $border = 0,
        $ln = 0,
        $align,
        $fill = 0,
        $link = '',
        $stretch = 0,
        $ignore_min_height = false,
        $calign = 'T',
        $valign = 'M');
    }
  }
  function outputTwoFields($x, $y, $fontSize, $width, $value1, $value2, $align='L', $lang='eng') {
    $value1 = trim($value1);
    $value2 = trim($value2);
    $segs = [];
    if(!empty($value1)) { $segs[] = $value1; }
    if(!empty($value2)) { $segs[] = $value2; }
    if(count($segs)>0) {
      $str = implode(', ', $segs);
      $this->outputText( $x, $y,
        $fontSize,
        $width,
        $str,
        $align,
        $lang);
    }

  }
}