<?php namespace App\Helpers;

class XmlValidatorHelper
{
  protected static $handler = null;
  /**
   * @var string
   */
  protected static $feedSchema = __DIR__ . '/sample.xsd';
  /**
   * @var int
   */
  public static $feedErrors = 0;
  /**
   * Formatted libxml Error details
   *
   * @var array
   */
  public static $errorDetails;

  /**
   * Validation Class constructor Instantiating DOMDocument
   *
   * @param \DOMDocument $handler [description]
   */

  /**
   * @param \libXMLError object $error
   *
   * @return string
   */
  private static function libxmlDisplayError($error)
  {
    $errorString = "Error $error->code in $error->file (Line:{$error->line}):";
    $errorString .= trim($error->message);
    return $errorString;
  }

  /**
   * @return array
   */
  private static function libxmlDisplayErrors()
  {
    $errors = libxml_get_errors();
    $result = [];
    foreach ($errors as $error) {
      $result[] = self::libxmlDisplayError($error);
    }
    libxml_clear_errors();
    return $result;
  }

  /**
   * Validate Incoming Feeds against Listing Schema
   *
   * @param resource $feeds
   *
   * @return bool
   *
   * @throws \Exception
   */
  public static function validateFeeds($feeds, $schemaFile)
  {
    self::$handler = new \XMLReader();
    if (!class_exists('XMLReader')) {
      throw new \DOMException("'XMLReader' class not found!");
      return false;
    }

    if (!file_exists($schemaFile)) {
      throw new \Exception('Schema is Missing, Please add schema to feedSchema property');
      return false;
    }

    self::$handler->open($feeds);
    self::$handler->setSchema($schemaFile);
    libxml_use_internal_errors(true);
    while (self::$handler->read()) {
      if (!self::$handler->isValid()) {
        self::$errorDetails = self::libxmlDisplayErrors();
        self::$feedErrors = 1;
      } else {
        return true;
      }
    };
  }

  /**
   * Display Error if Resource is not validated
   *
   * @return array
   */
  public static function displayErrors()
  {
    return self::$errorDetails;
  }
}
/* Examples
$validator = new XmlValidator;
$validated = $validator->validateFeeds('sample.xml','sample.xsd');
if ($validated) {
  echo "Feed successfully validated";
} else {
  print_r($validator->displayErrors());
}
*/