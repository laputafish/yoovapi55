<?php namespace App\Helpers\OA;

class OAAddressHelper {
  public static function parse($address, &$outputAddr, &$areaCode, $isEnglish=true) {
    // e.g. KLN-Kwun Tong-Wai Yip St.,-161-NAN-Tamson Plaza-NAN-23-2303

    // 0: [HK|KLN|NT]
    // 1: District: Kwun Tong
    // 2: Street Name: Wai Yip St.
    // 3: Street No.: 161
    // 4: Estate:
    // 5: Building Name: Tamson Plaza
    // 6: Block: NAN
    // 7: Floor: 23
    // 8: Room: 2303

    $segs = explode('-', $address);
    $region = $segs[0];
    $district = $segs[1];
    $streetName = $segs[2];
    $streetNo = $segs[3];
    $estate = $segs[4];
    $buildingName = $segs[5];
    $block = $segs[6];
    $floor = $segs[7];
    $room = $segs[8];

    $isEnglish = !hasChinese($address);
    switch($region) {
      case 'HK':
        $areaCode = 'H';
        break;
      case 'KLN':
        $areaCode = 'K';
        break;
      case 'NT':
        $areaCode ='N';
        break;
      default:
        $areaCode = 'F';
    }

    $addressSegs = [];
    if($isEnglish) {
      //***********************
      // English
      //***********************

      // Buliding Group
      $data = [];
      if(self::isValid($room)) $data[] = 'Room '.$room;
      if(self::isValid($floor)) $data[] = $floor.'/F';
      if(self::isValid($block)) $data[] = 'Block '.$block;
      if(self::isValid($buildingName)) $data[] = $buildingName;
      if(count($data)>0) {
        $addressSegs[] = implode(' ', $data);
      }

      // Estate
      if(self::isvalid($estate)) $addressSegs[] = $estate;

      // Street Group
      $data = [];
      if(self::isValid($streetNo)) $data[] = $streetNo;
      if(self::isValid($streetName)) $data[] = $streetName;
      if(count($data)>0) {
        $addressSegs[] = implode(' ', $data);
      }

      // District
      if(self::isValid($district)) $addressSegs[] = $district;

      $result = implode(', ', $addressSegs);
    } else {
      //***********************
      // Chinese
      //***********************

      if(self::isValid($district)) $addressSegs[] = $district;
      if(self::isValid($streetName)) $addressSegs[] = $streetName;
      if(self::isValid($streetNo)) $addressSegs[] = $streetNo;
      if(self::isValid($estate)) $addressSegs[] = $estate;
      if(self::isValid($buildingName)) $addressSegs[] = $buildingName;
      if(self::isValid($block)) $addressSegs[] = $block;
      if(self::isValid($floor)) $addressSegs[] = $floor;
      if(self::isValid($room)) $addressSegs[] = $room;

      $result = implode('', $addressSegs);
    }
    return $result;
  }

  private static function isValid($addrSeg)
  {
    return $addrSeg != '' && $addrSeg != 'NAN';
  }
}