<?php namespace App\Models;

class FormSalary extends BaseIRDForm {
  protected $modelName = 'FormSalary';
  protected $employeeModelName = 'FormSalaryEmployee';

  protected $fillable = [
    'team_id',
    'form_no',
    'form_date',
    'subject',
    'fiscal_year',
    'ird_form_id',
    'remark'
  ];

}