<?php namespace App\Helpers\IrData;

class IrdBaseXml extends \DomDocument {
  protected $root = null;
  protected $xsdFile = null;

  public function __construct($irdCode) {
    parent::__construct('1.0', 'UTF-8');
    $this->root = $this->appendChild($this->createElement(
      strtoupper($irdCode)
    ));

    $this->root->setAttribute(
      'xmlns:xsi',
      'http://www.w3.org/2001/XMLSchema-instance'
    );

    $this->root->setAttribute(
      'xsi:noNamespaceSchemaLocation',
      'ir56b.xsd'
    );

    // Enable user error handling
    libxml_use_internal_errors(true);

  }

  public function addChild($name, $value=null, $domElement=null) {
//    echo 'name = '.$name.' :: value='.$value; nf();
    if(isset($domElement)) {
      $child = $domElement->appendChild($this->createElement($name));
    } else {
      $child = $this->root->appendChild($this->createElement($name));
    }
    if(isset($value)) {
      $child->appendChild($this->createTextNode($value));
    }
    return $child;
  }

  public function validate()
  {
    if(isset($this->xsdFile)) {
      if (!$this->schemaValidate($this->xsdFile)) {
        print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
        $this->libxml_display_errors();
      } else {
        echo 'validated ok!';
      }
    }
    else {
      echo 'No xsdfile specified!';
    }
  }

  private function libxml_display_error($error)
  {
    $return = "<br/>\n";
    switch ($error->level) {
      case LIBXML_ERR_WARNING:
        $return .= "<b>Warning $error->code</b>: ";
        break;
      case LIBXML_ERR_ERROR:
        $return .= "<b>Error $error->code</b>: ";
        break;
      case LIBXML_ERR_FATAL:
        $return .= "<b>Fatal Error $error->code</b>: ";
        break;
    }
    $return .= trim($error->message);
    if ($error->file) {
      $return .=    " in <b>$error->file</b>";
    }
    $return .= " on line <b>$error->line</b>\n";

    return $return;
  }

  private function libxml_display_errors() {
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
      print $this->libxml_display_error($error);
    }
    libxml_clear_errors();
  }

}