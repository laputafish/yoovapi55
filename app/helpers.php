<?php
function nl() {
    echo "<br/>\n";
}

function logConsole($msg, $indentLevel=0) {
  if ($indentLevel > 0) {
    echo str_repeat('   ', $indentLevel);
  }
  echo $msg; nl();
}

function getLocalDateTime()
{
    $timezone = 'ASIA/HONG_KONG';
    $now = new \DateTime('now', new \DateTimeZone($timezone));
    $nowStr = $now->format('Y-m-d H:i:s');
    return $nowStr;
}

function getToday() {
  $timezone = 'ASIA/HONG_KONG';
  $now = new \DateTime('now', new \DateTimeZone($timezone));
  return $now->format('Y-m-d');
}

function getUniqId() {
  return uniqid() . '_' . md5(mt_rand());
}

function getIdArray($idStr) {
  $result = [];
  if(!empty(idStr)) {
    $result = explode(',', $idStr);
  }
  return $result;
}

function preg_replace_all( $find, $replacement, $s )
{
  while(preg_match($find, $s)) {
    $s = preg_replace($find, $replacement, $s);
  }
  return $s;
}

function toCurrency($value, $digits=0, $decimals='.', $thousands=',') {
  return number_format( $value, $digits, $decimals, $thousands);
}

function xxxxarray_sort($array, $on, $order=SORT_ASC)
{
  $new_array = array();
  $sortable_array = array();

  if (count($array) > 0) {
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        foreach ($v as $k2 => $v2) {
          if ($k2 == $on) {
            $sortable_array[$k] = $v2;
          }
        }
      } else {
        $sortable_array[$k] = $v;
      }
    }

    switch ($order) {
      case SORT_ASC:
        asort($sortable_array);
        break;
      case SORT_DESC:
        arsort($sortable_array);
        break;
    }

    foreach ($sortable_array as $k => $v) {
      $new_array[$k] = $array[$k];
    }
  }

  return $new_array;
}

function js2phpDate($jsDate) {
  $jsDateTS = strtotime(substr($jsDate, 0, 10));
  return date('Y-m-d', $jsDateTS);
}

function js2phpDateTime($jsDate) {

}

function getDMYSegs($dateStr) {
  $time = strtotime($dateStr);
  $segs = date('d-m-Y', $time);
  return explode('-', $segs);
}

function phpDateFormat( $yyyy_mm_dd, $format ) {
  $date = strtotime($yyyy_mm_dd);
  return date($format, $date);
}

function getOAEmployeeChineseName($oaEmployee) {
  $result = '';
  if(isset($oaEmployee)) {
    $values = [];
    if(!empty(trim($oaEmployee['chineseSurname']))) {
      $values[] = trim($oaEmployee['chineseSurname']);
    }
    if(!empty(trim($oaEmployee['chineseGivenName']))) {
      $values[] = trim($oaEmployee['chineseGivenName']);
    }
    $result = implode(' ', $values);
  }
  return $result;
}

function hasChinese($utf8_str) {
  return preg_match("/\p{Han}+/u", $utf8_str);
}

function decamelize($word) {
  return $word = preg_replace_callback(
    "/(^|[a-z])([A-Z])/",
    function($m) { return strtolower(strlen($m[1]) ? "$m[1]_$m[2]" : "$m[2]"); },
    $word
  );

}
function camelize($word) {
  return $word = preg_replace_callback(
    "/(^|_)([a-z])/",
    function($m) { return strtoupper("$m[2]"); },
    $word
  );
}

function getCurrentFiscalYearStartDate() {
  $today = date('Y-m-d');
  $year = date('Y');
  $fiscalYearStart = $year.'-04-01';
  return $today < $fiscalYearStart ?
    ($year-1).'-04-01' :
    $fiscalYearStart;
}

function getFiscalYearStartOfDate($theDate) {
  $dt = strtotime( $theDate );
  $year = (int) date('Y');
  $day = date('Y-m-d', $dt);
  $cutoffDate = date('Y', $dt).'-04-01';
  return $day < $cutoffDate ?
    ($year-1).'-04-01' :
    $cutoffDate;
}