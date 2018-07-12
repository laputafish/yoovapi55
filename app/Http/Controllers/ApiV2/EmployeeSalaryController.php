<?php namespace App\Http\Controllers\ApiV2;

class EmployeeSalaryController extends BaseIRDFormController {
  protected $modelName = 'FormDeparture';



  protected function prepareForm($form) {
    $form = parent::prepareForm($form);
    $today = getToday();
    $currentYear = date("Y");
    $cutoffDate = $currentYear.'-03-31';

    $form['fiscal_year'] = $today <= $cutoffDate ? $currentYear : $currentYear + 1;
    return $form;
  }


}