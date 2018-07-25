<?php namespace App\Helpers;

use App\Models\Team;
use App\Models\TeamEmployee;

class TeamHelper
{
  public static function getOrCreate($teamId) {
    $team = Team::whereOaTeamId( $teamId)->first();
    if (is_null($team)) {
      $team = Team::create([
        'oa_team_id' => $teamId
      ]);
    }
    return $team;
  }

  public static function updateEmployee( $team, $oaEmployee ) {
    $employee = $team->employees()->whereId($oaEmployee['id'])->first();
    if(is_null($employee)) {
      $team->employees()->save(new TeamEmployee([
        'id' => (int) $oaEmployee['id'],
        'last_name' => $oaEmployee['lastName'],
        'first_name' => $oaEmployee['firstName'],
        'display_name' => $oaEmployee['displayName'],
        'gender' => $oaEmployee['gender'],
        'job_title' => $oaEmployee['jobTitle'],
        'work_email' => $oaEmployee['workEmail'],
        'personal_email' => $oaEmployee['personalEmail'],
        'avatar' => $oaEmployee['avatar'],
        'avatar_url' => $oaEmployee['avatarUrl'],
        'office_phone' => $oaEmployee['officePhone'],
        'mobile_phone' => $oaEmployee['mobilePhone'],
        'remark' => $oaEmployee['remark'],
        'active' => $oaEmployee['active'],
        'status' => $oaEmployee['status'],
        'joined_date' => js2phpDate( $oaEmployee['joinedDate'] ).' 00:00:00',
        'deleted_at' => $oaEmployee['deletedAt']
      ]));
    } else {
      $employee->update([
        'last_name' => $oaEmployee['lastName'],
        'first_name' => $oaEmployee['firstName'],
        'display_name' => $oaEmployee['displayName'],
        'gender' => $oaEmployee['gender'],
        'job_title' => $oaEmployee['jobTitle'],
        'work_email' => $oaEmployee['workEmail'],
        'personal_email' => $oaEmployee['personalEmail'],
        'avatar' => $oaEmployee['avatar'],
        'avatar_url' => $oaEmployee['avatarUrl'],
        'office_phone' => $oaEmployee['officePhone'],
        'mobile_phone' => $oaEmployee['mobilePhone'],
        'remark' => $oaEmployee['remark'],
        'active' => $oaEmployee['active'],
        'status' => $oaEmployee['status'],
        'joined_date' => js2phpDate( $oaEmployee['joinedDate'] ).' 00:00:00',
        'deleted_at' => $oaEmployee['deletedAt']
      ]);
    }
  }
}