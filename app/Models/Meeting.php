<?php

namespace App\Models;

use App\Helpers\MeetingRoomHelper;

class Meeting extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'subject',
    'venue_type',
    'venue',
    'meeting_room_booking_id',
    'user_id',
    'started_at',
    'ended_at',
    'remark',
    'created_at'
  ];

  protected $appends = ['status','meeting_venue','applicant_name'];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  public function getStatusAttribute() {
    $sNow = getLocalDateTime();
    $started = $sNow >= $this->started_at;
    $ended = false;
    if(!is_null($this->ended_at)) {
      $ended = $sNow > $this->ended_at;
    }
    $status = 'Pending';
    if($started) {
      if($ended) {
        $status = 'Finished';
      }
      else {
        $status = 'In Progress';
      }
    }

    return $status;
  }

  public function RoomBooking() {
    return $this->belongsTo('App\Models\MeetingRoomBooking');
  }

  public function getBookingCountAttribute() {
    return $this->bookings()->count();
  }

  public function getMeetingVenueAttribute() {
    $result = $this->venue;
    if($this->venue_type == 'conference_room') {
      $result = $this->meetingRoom;
    }
    return $result;
  }

  public function meetingRoom() {
    $result = null;
    if(isset($this->roomBooking)) {
      $result = $this->roomBooking->meetingRoom;
    }
    return result;
  }

  public function applicant() {
    return $this->belongsTo('App\User');
  }

  public function getApplicantNameAttribute() {
    $result = '';
    if(isset($this->applicant)) {
      $result = $this->applicant->name;
    }
    return $result;
  }
}
