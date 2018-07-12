<?php namespace App\Models;

class TeamSetting extends BaseModel {
  protected $fillable = [
    'team_id',
    'key',
    'value'
  ];

  public function team() {
    return $this->belongsTo('App\Models\Team');
  }
}