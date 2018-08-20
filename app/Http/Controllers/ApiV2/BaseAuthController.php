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
  protected $esentialWith = null;
  protected $requireTeamFilter = true;

  public function __construct() {
    parent::__construct();
    $this->user = app('auth')->guard('api')->user();
    if(isset($this->user) && !empty($this->user->oa_last_team_id)) {
      $this->team = Team::whereOaTeamId($this->user->oa_last_team_id)->first();
    }
  }

  public function index()
  {
    $query = $this->model;
    if ($this->requireTeamFilter) {
      $query = $query->whereTeamId($this->team->id);
    }
    $query = isset($essentialWith) ? $this->model->with($this->essentialWith) : $this->model;
    $query = $this->addJoins($query);
    $query = $this->addFilter($query);
    $total = $query->count();

    $query = $this->addSortOrder($query);
    $data = $this->getWithPagination($query);

    return response()->json([
      'status' => true,
      'result' => [
        'data'=>$data,
        'total'=>$total
      ]
    ]);
  }

  protected function addJoins($query) {
    return $query;
  }

  public function getUser() {
    return request()->user();
  }

  protected function addFilter($query, $mapping=[]) {
    if (\Input::has('filter')) {
      $filter = \Input::get('filter');
      if(!empty($filter)) {
        $filters = explode(';', \Input::get('filter'));
        foreach ($filters as $filter) {
          $keyValues = explode(':', $filter);
          if ($keyValues[1] != '0') {
            $key = $keyValues[0];
            if(in_array($key, array_keys($mapping))) {
              $query = $query->where($mapping[$key], $keyValues[1]);
            } else {
              $query = $query->where($key, $keyValues[1]);
            }
          }
        }
      }
    }
    return $query;
  }

  protected function addSortOrder($query, $mapping=[]) {

    $sort = \Input::get('sort','');
    $order = \Input::get('order', 'asc');
    if(empty($sort) && !is_null($this->defaultSortOrder)) {
      $sort = $this->defaultSortOrder['sort'];
      $order = $this->defaultSortOrder['order'];
    }
    if(!empty($sort)) {
      if(in_array($sort, array_keys($mapping))) {
        $sort = $mapping[$sort];
      }
      $query = $query->orderby($sort, $order);
    }
    return $query;
  }

  protected function getWithPagination($query) {
//    $total = 0;
    // Pagination
    $query = $query->skip(\Input::get('offset',0));
    if(\Input::has('limit')) {
      $query = $query->take(\Input::get('limit'));
    }
    $data = $query->get();
    $data = $this->onDataReady($data);
//    $total = $data->count();
    return $data;
  }

  protected function onDataReady($data) {
    return $data;
  }
}