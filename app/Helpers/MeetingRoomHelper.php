<?php
namespace App\Helpers;

use App\Models\MeetingRoomBooking;

class MeetingRoomHelper {
  public static function getOccupiedMeetingRoomIds()
  {
    $sNow = getLocalDateTime();
    $occupied = MeetingRoomBooking::where('from_datetime', '<=', $sNow)
      ->where('to_datetime', '>=', $sNow)
      ->pluck('id')
      ->toArray();
    return array_unique($occupied);
  }

  public static function withBookings() {

  }
}