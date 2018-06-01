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
    'applicant_name',
    'description',
    'applicant_id',
    'started_at',
    'ended_at',
    'remark'
  ];

  public $appends = [
    'applicant_name',
    'meeting_room_name'
  ];

  public function applicant() {
    return $this->belongsTo( 'App\User', 'applicant_id');
  }

  public function getApplicantNameAttribute() {
    return isset($this->applicant) ? $this->applicant->name : '(Undefined)';
  }

  public function meetings() {
    return $this->hasMany('App\Models\Meeting', 'meeting_room_booking_id');
  }

  public function meetingRoom() {
    return $this->belongsTo('App\Models\MeetingRoom', 'meeting_room_id');
  }

  public function getMeetingRoomNameAttribute() {

    return isset($this->meetingRoom) ?
      $this->meetingRoom->name :
      '';
  }
}