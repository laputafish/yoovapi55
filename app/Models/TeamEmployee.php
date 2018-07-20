<?php namespace App\Models;

class TeamEmployee extends BaseModel
{
  public $autoincrement = false;

  protected $fillable = [
    'id',
    'team_id',
    'last_name',
    'first_name',
    'display_name',
    'gender',
    'job_title',
    'work_email',
    'personal_email',
    'avatar',
    'avatar_url',
    'office_phone',
    'mobile_phone',
    'remark',
    'active',
    'status',
    'joined_date',
    'job_ended_date',
    'deleted_at'
  ];

  public function team() {
    return $this->belongsTo('App\Models\Team');
  }
}