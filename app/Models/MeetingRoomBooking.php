<?php

namespace App\Models;

use App\Helpers\MeetingRoomHelper;

class MeetingRoomBooking extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'meeting_room_id',
    'applicant_id',
    'from_datetime',
    'to_datetime',
    'remark',
    'remark'
  ];

  public function meetings() {
    return $this->hasMany('App\Models\Meeting');
  }

  public function meetingRoom() {
    return $this->belongsTo('App\Models\MeetingRoom');
  }
}