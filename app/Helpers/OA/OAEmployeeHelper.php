<?php namespace App\Helpers\OA;

use App\Models\TeamJob;
use App\Helpers\CurlHelper;
use App\Events\xxxTaxFormStatusUpdatedEvent;

class OAEmployeeHelper
{

  public static function getAdminInfo($oaAuth, $employeeId, $oaTeamId)
  {
    $url = \Config::get('oa')['apiUrl'] . '/admin/employees/' . $employeeId . '?include=nationality&teamId=' . $oaTeamId;
    return OAHelper::get($url, $oaAuth);
  }

  public static function get($oaAuth, $employeeId, $oaTeamId)
  {
    $url = \Config::get('oa')['apiUrl'] . '/user/employees/' . $employeeId . '?include=nationality&teamId=' . $oaTeamId;
    return OAHelper::get($url, $oaAuth);
  }

  public static function getCommencementSalary($joinedDate, $salaries)
  {
    $salaryList = [];

    foreach ($salaries as $salary) {
      $salaryList[] = [
        'effectiveDate' => $salary['effectiveDate'],
        'payRate' => $salary['payRate']
      ];
    }
    $salaryList = array_sort($salaryList, function( $salaryItem ) {
      return $salaryItem['effectiveDate'];
    });

    $result = 0;

    if(count($salaries)>0) {
      $result = (double)$salaryList[0]['payRate'];

      foreach($salaryList as $salaryItem) {
        if($joinedDate >= $salaryItem['effectiveDate']){
          $result = (double) $salaryItem['payRate'];
        } else {
          break;
        }
      }
    }
    return $result;
  }
}