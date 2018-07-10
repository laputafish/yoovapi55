<?php namespace App\Http\Controllers\ApiV2;

use App\Models\FormCommencementEmployee;

class EmployeeCommencementController extends BaseIRDFormController {
  protected $modelName = 'FormCommencement';

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
}