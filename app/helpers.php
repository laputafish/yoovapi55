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
