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
    'started_at',
    'ended_at',
    'remark',
    'remark'
  ];

  public $appends = [
    'applicant_name'
  ];

  public function applicant() {
    return $this->belongsTo( 'App\User', 'applicant_id');
  }

  public function getApplicantNameAttribute() {
    return $this->applicant->name;
  }

  public function meetings() {
    return $this->hasMany('App\Models\Meeting');
  }

  public function meetingRoom() {
    return $this->belongsTo('App\Models\MeetingRoom');
  }
}