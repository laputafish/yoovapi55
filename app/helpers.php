<?php
function nl() {
    echo '<br/>';
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