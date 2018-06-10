<?php namespace App\Helpers;

use App\Models\Equipment;

class EquipmentHelper {
  public static function getSetting($equipmentName, $key)
  {
    $result = null;
    $equipment = Equipment::whereName( $equipmentName )->first();
    if (isset($equipment)) {
      $settings = json_decode($equipment->settings, true);
    }
    return $settings[$key];
  }
}