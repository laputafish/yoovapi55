<?php namespace App\Http\Controllers\ApiV2;

use App\Models\Team;
use \Carbon\Carbon;

class TeamController extends BaseAuthController
{
  protected $modelName = 'Team';
  protected $requireTeamFilter = false;

  public function show($id) {
    $includeStr = \Input::get('include');
    $includes = explode(',', $includeStr);
    $team = Team::find($id);
    $includeItems = [];
    if(array_key_exists('settings', $includes)) {
      $includeItems['fileNo'] = $team->getSetting('fileNo', '');
    }
    $result = array_merge(
      $team->toArray(),
      $includeItems
    );
    return response()->json([
      'status'=>true,
      'result'=>$result
    ]);
  }

  public function store() {
    $teams = \Input::get('teams', []);
    if(!empty($teams)) {
      foreach($teams as $team) {
        $dbTeam = Team::whereOaTeamId($team['id'])->first();
        if(isset($dbTeam)) {
          $dbTeam->oa_team_currency_code = $team['currencyCode'];
          $dbTeam->oa_team_logo_path = $team['logoUrl'];
          $dbTeam->oa_team_name = $team['name'];
          $dbTeam->oa_team_created_at = Carbon::parse($team['createdAt']);
          $dbTeam->save();
        } else {
          Team::create([
            'oa_team_id'=>$team['id'],
            'oa_team_code'=>$team['code'],
            'oa_team_currency_code'=>$team['currencyCode'],
            'oa_team_logo_path'=>$team['logoUrl'],
            'oa_team_name'=>$team['name'],
            'oa_team_created_at'=>Carbon::parse($team['createdAt'])
          ]);
        }
      }
    }

    return response()->json([
      'status'=>true
    ]);
  }
}