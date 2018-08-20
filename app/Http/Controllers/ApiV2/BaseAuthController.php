<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TaxForm;
use App\Models\TeamJob;
use App\Models\Team;

use App\Helpers\TeamHelper;
use App\Helpers\TeamJobHelper;

use App\Events\xxxTaxFormNewJobEvent;
use App\Events\xxxTaxFormStatusUpdatedEvent;

class BaseAuthController extends BaseController {
  protected $team = null;
  protected $defaultSortOrder = null;

  public function __construct() {
    parent::__construct();
    $this->user = app('auth')->guard('api')->user();
    if(isset($this->user) && !empty($this->user->oa_last_team_id)) {
      $this->team = Team::whereOaTeamId($this->user->oa_last_team_id)->first();
    }
  }

  public function getUser() {
    return request()->user();
  }

  public function addFilter($query) {
    if (\Input::has('filter')) {
      $filters = explode(';', \Input::get('filter'));
      foreach($filters as $filter) {
        $keyValues = explode(':', $filter);
        if($keyValues[1]!='0') {
          $query = $query->where($keyValues[0], $keyValues[1]);
        }
      }
    }
    return $query;
  }

  public function addSortOrder( $query) {
    $sort = \Input::get('sort','');
    $order = \Input::get('order', 'asc');
    if(empty($sort) && !is_null($this->defaultSortOrder)) {
      $sort = $this->defaultSortOrder['sort'];
      $order = $this->defaultSortOrder['order'];
    }
    $query = $query->orderby($sort, $order);
    return $query;
  }

  public function getWithPagination($query, &$total) {
    $total = 0;
    // Pagination
    $query = $query->skip(\Input::get('offset',0));
    if(\Input::has('limit')) {
      $query = $query->take(\Input::get('limit'));
    }
    $data = $query->get();
    $data = $this->onDataReady($data);
    $total = $data->count();
    return $data;
  }
}