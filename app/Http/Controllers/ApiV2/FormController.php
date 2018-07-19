<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\TaxFormHelper;

class FormController extends BaseAuthController {
  protected $modelName = 'Form';

  protected $BLANK_FORM = [
    'id'=>0,
    'team_id'=>'',
    'form_no'=>'',
    'form_date'=>'',
    'lang_id'=>0,
    'status'=>'pending',
    'subject'=>'',
    'ird_form_type_id'=>0,
    'ird_form_id'=>0,
    'remark'=>'',
    'fiscal_start_year'=>0,
    'employees'=>[]
  ];

  public function __construct() {
    parent::__construct();
    $this->BLANK_FORM['team_id'] = $this->user->oa_last_team_id;
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
    $prefix = $this->team->getSetting( 'form_prefix', 'IR' );
    $form['form_no'] = TaxFormHelper::getNextFormId($this->model, $prefix);
    $form['form_date'] = getToday();
    $form['team_id'] = $this->team->id;
    $form['fiscal_start_year'] = getLastValidFiscalStartYear();
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
