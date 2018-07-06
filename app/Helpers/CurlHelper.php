<?php namespace App\Helpers;

class CurlHelper
{
  public static function getData($url, $header) {
    $cookie="cookie.txt";

    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt ($ch, CURLOPT_REFERER, $url);
    $result = self::getResult($ch);
    curl_close($ch);
    return $result;
  }

  public static function postData($url, $header, $dataStr) {
    $cookie="cookie.txt";

    // execution
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt ($ch, CURLOPT_REFERER, $url);

    curl_setopt ($ch, CURLOPT_POSTFIELDS, $dataStr);
    curl_setopt ($ch, CURLOPT_POST, 1);
    $result = self::getResult($ch);
    curl_close($ch);
    return $result;
  }

  public static function getResult($ch) {
    try {
      $jsonStr = curl_exec($ch);
    } catch(ErrorException $e) {
      $jsonStr = FALSE;
    }
    if ($jsonStr === FALSE) {
      $result = FALSE;
    } else {
      $result = json_decode($jsonStr, true);
    }
    return $result;
  }

  public static function get($url, $header)
  {
    $cookie="cookie.txt";

    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt ($ch, CURLOPT_REFERER, $url);
    $result = curl_exec ($ch);

    curl_close($ch);


    return $result;
  }

  public static function post($url, $postData, $header) {
    $cookie = "cookie.txt";
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
    curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt ($ch, CURLOPT_REFERER, $url);

    curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt ($ch, CURLOPT_POST, 1);
    $result = curl_exec ($ch);

    curl_close($ch);
    return $result;
  }

}