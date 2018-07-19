<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TaxForm;
use App\Models\TeamJob;
use App\Models\Team;

use App\Helpers\TeamHelper;
use App\Helpers\TeamJobHelper;

use App\Events\TaxFormNewJobEvent;
use App\Events\TaxFormStatusUpdatedEvent;

class BaseAuthController extends BaseController {
  public function __construct() {
    parent::__construct();
    $this->user = app('auth')->guard('api')->user();
    $this->team = Team::whereOaTeamId($this->user->oa_last_team_id)->first();
  }

  public function getUser() {
    return request()->user();
  }

}