<?php namespace App\Http\ApiV2;

class TeamController extends BaseAuthController
{
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
}