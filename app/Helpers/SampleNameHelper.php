<?php namespace App\Helpers;

class SampleNameHelper {
  public static function get($sex=null)
  {
    // Surname
    $surnameChnEng = getRandomItem(\Config::get('sample.surnames'));

    $chnSurname = $surnameChnEng[0];
    $engSurname = $surnameChnEng[1];

    if(is_null($sex)) {
      $sex = rand(0,1)==0 ? 'M' : 'F';
    }

    // Given Name (English)
    if($sex == 'M') {
      $engGivenName = getRandomItem(\Config::get('sample.engGivenNames')[0]);
      $chnGivenName = getRandomItem(\Config::get('sample.chnGivenNames')[0]);
    } else {
      $engGivenName = getRandomItem(\Config::get('sample.engGivenNames')[1]);
      $chnGivenName = getRandomItem(\Config::get('sample.chnGivenNames')[1]);
    }
    return [
      'sex'=>$sex,
      'surname'=>$engSurname,
      'givenName'=>$engGivenName,
      'nameInChinese'=>$chnSurname.$chnGivenName
    ];
  }


}