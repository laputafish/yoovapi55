<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\TaxFormHelper;
use App\Helpers\EventHelper;
use App\Helpers\OA\OAHelper;
use App\Helpers\FormHelper;

use App\Models\FormEmployee;
use App\Models\Lang;

class FormController extends BaseAuthController {
  protected $modelName = 'Form';

  protected $rules = [
    'team_id'=>'string',
    'form_no'=>'string',
    'form_date'=>'date',

    'lang_id'=>'integer',
    'status'=>'string',
    'subject'=>'string',

    'ird_form_type_id'=>'integer',
    'ird_form_id'=>'integer',
    'fiscal_start_year'=>'integer',

    'remark'=>'string',
    'signature_name'=>'string',
    'designation'=>'string',

    'submitted_on'=>'string'
  ];

  protected $BLANK_FORM = [
    'id'=>0,
    'team_id'=>'',
    'form_no'=>'',
    'form_date'=>'',
    'lang_id'=>0,
    'status'=>'pending',

    'subject'=>'',
    'published'=>0,
    'ird_form_type_id'=>0,
    'ird_form_id'=>0,
    'remark'=>'',
    'signature_name'=>'',
    'designation'=>'',
    'fiscal_start_year'=>0,
    'submitted_on'=>'',
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
      $result->files = $result->getAttachments();
    }
    return $result;
  }

  protected function prepareForm($form) {
    $prefix = $this->team->getSetting( 'form_prefix', 'IR' );
    $langCode = $this->team->getSetting('lang', 'en-us');
    $lang = Lang::whereCode($langCode)->first();
    $form['form_no'] = TaxFormHelper::getNextFormId($this->model, $prefix);
    $form['form_date'] = getToday();
    $form['team_id'] = $this->team->id;
    $form['lang_id'] = $lang->id;
    $form['designation'] = $this->team->getSetting('designation', '');
    $form['signature_name'] = $this->team->getSetting('signatureName', '');

    $form['fiscal_start_year'] = getLastValidFiscalStartYear();
    return $form;
  }

  public function index() {
    $input = \Input::all();
    $query = $this->model->whereTeamId($this->team->id)->with('employees');
    $total = $query->count();

    // filter
    if (\Input::has('filter')) {
      $filters = explode(';', \Input::get('filter'));
      foreach($filters as $filter) {
        $keyValues = explode(':', $filter);
        if($keyValues[1]!='0') {
          $query = $query->where($keyValues[0], $keyValues[1]);
        }
      }
    }

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

  public function update($id) {
    $form = $this->model->find($id);
    $input = $this->getInput();

    $employees = \Input::get('employees',[]);
    if(!is_null($input['submitted_on'])) {
      $input['status'] = 'completed';
    }
    $form->update($input);
    $dataEmployeeIds = $form->employees()->pluck('employee_id')->toArray();
    $inputEmployeeIds = array_map(function($formEmployee) {
      return (int) $formEmployee['employee_id'];
    }, $employees);

    $newIds = array_values(array_diff($inputEmployeeIds, $dataEmployeeIds));
    $obsolateIds = array_values(array_diff($dataEmployeeIds, $inputEmployeeIds));
    $form->employees()->whereIn('employee_id', $obsolateIds)->delete();
    for($i=0; $i<count($newIds); $i++) {
      $form->employees()->save(new FormEmployee([
        'employee_id' => $newIds[$i]
      ]));
    }

    return response()->json([
      'status'=>true,
      'result'=>[
        'id' => $form->id,
        'added_ids'=>$newIds,
        'removed_ids'=>$obsolateIds
      ]
    ]);
  }

  public function store() {
    if(\Input::has('command')) {
      return $this->processCommand(\Input::get('command'));
    }
    else {
      $input = $this->getInput();
      $form = $this->model->create($input);

      $formEmployees = \Input::get('employees', []);
      $formEmployeeIds = array_map(function ($formEmployee) {
        return (int)$formEmployee['employee_id'];
      }, $formEmployees);

      for ($i = 0; $i < count($formEmployeeIds); $i++) {
        $form->employees()->save(new FormEmployee([
          'employee_id' => $formEmployeeIds[$i]
        ]));
      }

      return response()->json([
        'status' => true,
        'result' => [
          'id' => $form->id,
          'added_ids' => $formEmployeeIds
        ]
      ]);
    }
  }

  protected function processCommand( $command ) {
    $formId = \Input::get('formId');
    $form = $this->model->find($formId);
    if(is_null($form)) {
      return response()->json([
        'status'=>false,
        'result'=>''
      ]);
    }

    OAHelper::refreshTeamToken($this->user, $this->team);
    $newStatus = '';
    switch ($command) {
      case 'generate':
        $newStatus = 'ready_for_processing';
        $update = [
          'status'=>$newStatus,
          'sheet_no'=>0
        ];
        $form->prepareFolder();
        $form->update($update);
        $form->employees()->update($update);
        break;
      case 'terminate':
        $newStatus = 'terminated';
        $update = ['status'=>$newStatus];
        $form->update($update);
        $form->employees()->where('status', '!=', 'ready')->update($update);
        break;
    }
    EventHelper::send('form', ['form'=>$form]);

//    $sheetNo = 1;
    foreach($form->employees as $formEmployee) {
//        $form->employees()->whereEmployeeId( $formEmployee->employee_id )->update(['sheet_no'=>$sheetNo]);
      EventHelper::send('formEmployee', [
        'form'=>$form,
        'formEmployee'=>$formEmployee]);
//      $sheetNo++;
    }

    return response()->json([
      'status'=>true,
      'result'=>$newStatus
    ]);
  }

  public function destroy($id) {
    $form = $this->model->find($id);
    FormHelper::removeIrdFormFiles($form);
    $form->employees()->delete();
    $form->delete();
    return response()->json([
      'status'=>true
    ]);
  }

  protected function onDataReady($data) {
    foreach($data as $row) {
      $row->form_type = trans('tax.'.strtolower($row->irdFormType->name ));
    }
    return $data;
  }

}
