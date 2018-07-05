<?php namespace App\Helpers;

use App\Models\Team;

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
}