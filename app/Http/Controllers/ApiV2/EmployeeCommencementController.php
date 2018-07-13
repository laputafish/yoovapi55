<?php namespace App\Http\Controllers\ApiV2;

use App\Models\FormCommencementEmployee;

class EmployeeCommencementController extends BaseIRDFormController {
  protected $modelName = 'FormCommencement';
  protected $rules = [
    'team_id'=>'string',
    'form_no'=>'string',
    'form_date'=>'date',
    'status'=>'string',
    'subject'=>'string',
    'ird_form_id'=>'integer',
    'remark'=>'string',
    'submitted_on'=>'string'
  ];
  protected $formType = 'commencement';

  protected $FORM_CODE = 'IR56E';

  public function indexx() {
    $rows = $this->model->all();
    foreach( $rows as $i=>$row ) {
      echo 'form id = '.$row->id; nl();
      $k = $i<20 ? $i : 20;
      for($j=1; $j<=$k; $j++) {
        $employee = FormCommencementEmployee::whereFormId($row->id)->whereEmployeeId($j)->first();
////        echo 'check employee exists ...'; nl();
        if(is_null($employee)) {
          echo '   employee is null'; nl();
          $employee = new FormCommencementEmployee;
          $employee->employee_id = $j;
          $row->employees()->save($employee);
        }
////        else {
////          echo '   employee exists.'; nl();
////        }
      }
    }

    return parent::index();
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

    $newIds = array_diff($inputEmployeeIds, $dataEmployeeIds);
    $obsolateIds = array_diff($dataEmployeeIds, $inputEmployeeIds);
    $form->employees()->whereIn('employee_id', $obsolateIds)->delete();
    for($i=0; $i<count($newIds); $i++) {
      $form->employees()->save(new FormCommencementEmployee([
        'employee_id' => $newIds[$i]
      ]));
    }

    return response()->json([
      'status'=>true,
      'result'=>[
        'added_ids'=>$newIds,
        'removed_ids'=>$obsolateIds
      ]
    ]);

  }

  public function store() {
    $input = $this->getInput();
    $form = $this->model->create($input);

    $formEmployees = \Input::get('employees',[]);
    $formEmployeeIds = array_map(function($formEmployee) {
      return (int) $formEmployee['employee_id'];
    }, $formEmployees);

    for($i=0; $i<count($formEmployeeIds); $i++) {
      $form->employees()->save(new FormCommencementEmployee([
        'employee_id' => $formEmployeeIds[$i]
      ]));
    }

    return response()->json([
      'status'=>true,
      'result'=>[
        'added_ids'=>$formEmployeeIds
      ]
    ]);
  }
}