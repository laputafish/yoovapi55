<?php namespace App\Models;

class BaseIRDForm extends BaseModel {
  protected $modelName = '';
  protected $employeeModelName = '';
  protected $fillable = [
    'team_id',
    'form_no',
    'form_date',
    'subject',
    'ird_form_id',
    'remark'
  ];

  protected $appends = [
    'employees',
    'employee_count'
  ];

  public function getEmployeeCountAttribute() {
    return $this->employees()->count();
  }

  public function getEmployeesAttribute() {
    $employees = $this->employees()->get();
    return isset($employees) ? $employees->toArray() : [];
  }

  public function team() {
    return $this->belongsTo('App\Models\Team', 'team_id');
  }

  public function employees() {
    return $this->hasMany("App\\Models\\".$this->employeeModelName, 'form_id');
  }

  public function irdForm() {
    return $this->belongsTo( 'App\Models\IrdForm', 'ird_form_id');
  }
}