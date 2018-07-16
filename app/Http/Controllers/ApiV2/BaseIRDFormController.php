<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\TaxFormHelper;

use App\Models\Team;
// use App\Http\CustomPaginator as Paginator;

class BaseIRDFormController extends BaseAuthController {
  protected $BLANK_FORM = [
    'id'=>0,
    'team_id'=>'',
    'form_no'=>'',
    'form_date'=>'',
    'status'=>'pending',
    'subject'=>'',
    'ird_form_id'=>0,
    'remark'=>'',
    'employees'=>[]
  ];
  protected $FORM_CODE = 'IR56E';
  protected $formType = 'default';

//  protected $paginator;
//  protected $perPage = 10;

  public function __construct() {
    parent::__construct();
    $this->BLANK_FORM['team_id'] = $this->user->oa_last_team_id;
    $this->BLANK_FORM['ird_form_id'] = TaxFormHelper::getIrdFormId($this->FORM_CODE);
  }

  protected function onShowRecordReady($record) {
    $result = $record;
    if(is_null($record)) {
      $result = $this->prepareForm($this->BLANK_FORM);
    } else {
      $result->employees;
    }
    return $result;
  }

  protected function prepareForm($form) {
    $prefix = $this->team->getSetting( $this->formType.'_prefix', substr(strtoupper($this->formType),0,1));
    $form['form_no'] = TaxFormHelper::getNextFormId($this->model, $prefix);
    $form['form_date'] = getToday();
    $form['team_id'] = $this->team->id;
    return $form;
  }

  public function index() {
    $input = \Input::all();
    $query = $this->model->whereTeamId($this->team->id)->with('employees');
    $total = $query->count();

    // sort/order
    $sort = \Input::get('sort','');
    $order = \Input::get('order', 'asc');
    if(empty($sort)) {
      $sort = 'form_date';
      $order = 'desc';
    }
    $query = $query->orderby($sort, $order);

    // Pagination
    $query = $query->skip(\Input::get('offset',0));
    if(\Input::has('limit')) {
      $query = $query->take(\Input::get('limit'));
    }
    $data = $query->get();
    $data = $this->onDataReady($data);

    return response()->json([
      'status'=>true,
      'result'=>[
        'data'=>$data,
        'total'=>$total
      ]
    ]);
  }

  protected function onDataReady($data) {
    return $data;
  }

}