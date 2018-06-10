<?php

namespace App\Models;

use App\Helpers\MeetingRoomHelper;

class MeetingRoom extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'capacity',
    'location',
    'remark',
    'equipments'
  ];

  protected $appends = ['status','booking_count'];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function getStatusAttribute() {
    $occupiedMeetingRoomIds = MeetingRoomHelper::getOccupiedMeetingRoomIds();
    return in_array($this->id, $occupiedMeetingRoomIds)
      ? 'occupied'
      : 'vacant';
  }

  public function bookings() {
    return $this->hasMany('App\Models\MeetingRoomBooking', 'meeting_room_id');
  }

  public function getBookingCountAttribute() {
    return $this->bookings()->count();
  }

}
