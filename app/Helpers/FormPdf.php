<?php namespace App\Helpers;

use setasign\Fpdi;

class FormPdf extends Fpdi\TcpdfFpdi
{
  protected $tplId;
  protected $fontName = 'times';
  protected $fontNameChn = 'msungstdlight';
  protected $fontNameSymbol = 'zapfdingbats';
  protected $topOffset = 0;
  protected $rightMargin = 0;
  protected $fields = [];
  protected $dataItems = [];

  private function getOption($options, $key, $default) {
    return array_key_exists($key, $options) ?
      $options[$key] : $default;
  }

  public function __construct($options=[]) {
//    unset($options['fields']);
//    echo '__construct:: before parent __construct'; nf();
//    print_r( $options ); nf();
    parent::__construct();
//    echo '__construct:: after parent __construct'; nf();
//    print_r( $options ); nf();
    $this->AddFont('times', 'B', 'timesb.php');
    $this->setPrintHeader($this->getOption($options, 'printHeader', false));
    $this->SetPrintFooter($this->getOption($options, 'printFooter', false));
    $this->SetHeaderMargin($this->getOption($options, 'headerMargin', 0));
    $this->SetFooterMargin($this->getOption($options, 'headerMargin', 5));
    $this->SetAutoPageBreak($this->getOption($options, 'autoPageBreak', False));

    // options
    if(array_key_exists('topOffset', $options) ) {
      $this->topOffset = $options['topOffset'];
    }
    if(array_key_exists('rightMargin', $options) ) {
      $this->rightMargin = $options['rightMargin'];
    }
    if(array_key_exists('title', $options)) {
      $this->setTitle( $options['title'] );
      $this->setSubject( $options['title'] );
    }
    if(array_key_exists('fields', $options)) {
      $this->fields = $options['fields'];
    }
    if(array_key_exists('data', $options)) {
      $this->dataItems = $options['data'];
    }

    $this->AddPage();

    if(array_key_exists('templateFilePath',  $options)) {
      $this->setSourceFile( $options['templateFilePath'] );
      $tplId = $this->importPage(1);
      $this->useTemplate($tplId);
    }

  }

  function Header() {
    $this->outputData();
  }
  function Footer() {}

  function outputData() {
    foreach($this->dataItems as $fieldName=>$fieldValue) {
      $fieldConfig = $this->getFieldConfig($fieldName);
      $this->outputDataItem($fieldConfig, $fieldValue);
    }
  }

  function outputDataItem($fieldConfig, $text) {
    if($fieldConfig->hidden) {
      return;
    }
    $align = isset($fieldConfig->align) ? $fieldConfig->align : 'L';
    $lang = isset($fieldConfig->lang) ? $fieldConfig->lang : 'eng';
    $fontStyle = isset($fieldConfig->font_style) ? $fieldConfig->font_style : '';
    switch ($fieldConfig->type) {
      case 'string':
        if($text == '0') {
          if ($fieldConfig->blank_if_zero) {
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
        $x = $fieldConfig->x;
        $y = $fieldConfig->y;
        $width = $fieldConfig->width;
        $fontSize = $fieldConfig->font_size;
        $appendAsterisk = isset($fieldConfig->append_asterisk) ? $fieldConfig->append_asterisk : false;

        // Check is currency
        if(!empty($text)) {
          if($fieldConfig->to_currency) {
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
        $this->outputText(
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
  
  function getFieldConfig( $fieldName ) {
    $result = null;
    foreach( $this->fields as $field ) {
      if($field->key == $fieldName) {
        $result = $field;
        break;
      }
    }
    return $result;
  }

  function outputText($x, $y, $fontSize, $width, $text, $align='L', $lang='eng',$valign='M',$fontStyle='') {

    switch($lang) {
      case 'eng':
        $fontName = $this->fontName;
        break;
      case 'chn':
        $fontName = $this->fontNameChn;
        break;
      case 'symbol':
        $fontName = $this->fontNameSymbol;
        break;
      default:
        $fontName = $this->fontName;
    }

    if($width == 0) {
      $width = 200 - $x - $this->rightMargin;
    }

    $this->setY($this->topOffset + $y);
    $this->setX($x);

//    if($fontStyle == 'B') {
//      $this->setStyle('b', true);
//    } else {
//      $this->setStyle('b', false);
//    }

    //$this->SetFont($fontName, strtolower($fontStyle), $fontSize);
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
      $valign);
  }

  function outputChars($x, $y, $fontSize, $width, $charCount, $chars, $align='L', $lang='eng') {
    $fontName = $lang == 'eng' ? $this->fontName : $this->fontNameChn;

    $this->setY($this->topOffset + $y);
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

    $this->setY($this->topOffset + $y);
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

    $this->setY($this->topOffset + $y);
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