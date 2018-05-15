<?php
namespace App\Helpers;

use App\Models\MeetingRoomBooking;

class MeetingRoomHelper {
  public static function getOccupiedMeetingRoomIds()
  {
    $sNow = getLocalDateTime();
    $occupied = MeetingRoomBooking::where('started_at', '<=', $sNow)
      ->where('ended_at', '>=', $sNow)
      ->pluck('id')
      ->toArray();
    return array_unique($occupied);
  }

  public static function withBookings() {

  }
}