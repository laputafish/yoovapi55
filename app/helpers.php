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

function getUniqId() {
  return uniqid() . '_' . md5(mt_rand());
}