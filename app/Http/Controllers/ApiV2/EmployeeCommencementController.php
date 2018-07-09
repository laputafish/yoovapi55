<?php namespace App\Http\Controllers\ApiV2;

class EmployeeCommencementController extends BaseIRDFormController {
  protected $modelName = 'FormCommencement';

  public function index() {
    $rows = $this->model->all();
    foreach( $rows as $i=>$row ) {
      for($j=1; $j<=$i; $j) {
        $row->employees()->save(new FormCommencementEmployee([
          'employee_id'=>$j
        ]));
      }
    }


    return parent::index();
  }
}