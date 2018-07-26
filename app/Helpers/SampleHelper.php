<?php namespace App\Helpers;

class SampleHelper {
  public static function getHkid() {
    $prefix = chr(65 + rand(0,25));
    $body = rand(100000,999999);
    $suffix = rand(0,9);
    return $prefix.$body.$suffix;
  }

  public static function getMartialStatus($sex) {
    $married = rand(0,1);
    $result = [
      'married' => $married,
      'spouse' => null
    ];
    if($married == 1) {
      $spouseSex = $sex == 'M' ? 'F' : 'M';
      $spouseName = SampleNameHelper::get($spouseSex);
      $withHkid = rand(0,100) > 10;
      $result['spouse'] = [
        'name'=>$spouseName['surname'].', '.$spouseName['givenName'],
        'hkid'=>$withHkid ? self::getHkid() : '',
        'ppNum'=>$withHkid ? '' : 'PP1234567 (British)'
      ];
    }

    return $result;
  }

  public static function getCapacity() {
    $capacities = [
      'Director',
      'Accountant',
      'Programmer',
      'Project Coordinator',
      'Administration Clerk',
      'Marketing Manager',
      'Office Assistant',
      'Managing Director',
      'Chief Executive Officer',
      'Chief Technical Officer',
      'Web Developer'
    ];
    return getRandomItem($capacities);
  }

  public static function getEmploymentPeriod($fiscalStartYear) {
    return [
      'startDate'=>$fiscalStartYear.'-04-01',
      'endDate'=>($fiscalStartYear+1).'-03-31'
    ];
  }

  public static function getOtherRaps() {
    $result = [
      ['amt'=>0, 'nature'=>''],
      ['amt'=>0, 'nature'=>''],
      ['amt'=>0, 'nature'=>'']
    ];
    $natures = ['Other Rewards', 'Allowances', 'Perquisites'];
    shuffle($natures);

    if(rand(0,100)>80) {
      $result[0]['amt'] = rand(1,10)*1000;
      $result[0]['nature'] = $natures[0];
      if(rand(0,100)>80) {
        $result[1]['amt'] = rand(1,10)*1000;
        $result[1]['nature'] = $natures[1];
        if(rand(0,100)>80) {
          $result[2]['amt'] = rand(1, 10) * 1000;
          $result[2]['nature'] = $natures[2];
        }
      }
    }
    return $result;
  }

  public static function getResPlaceInfo() {
    return [
      'placeOfResInd'=>0,
      'places'=>[
        [
          'addr_of_place'=>'',
          'nature_of_place'=>'',
          'per_of_place'=>'',
          'rent_paid_er'=>0,
          'rent_paid_ee'=>0,
          'rent_refund'=>0,
          'rent_paid_er_by_ee'=>0
        ],
        [
          'addr_of_place'=>'',
          'nature_of_place'=>'',
          'per_of_place'=>'',
          'rent_paid_er'=>0,
          'rent_paid_ee'=>0,
          'rent_refund'=>0,
          'rent_paid_er_by_ee'=>0
        ]
      ]
    ];
  }
}