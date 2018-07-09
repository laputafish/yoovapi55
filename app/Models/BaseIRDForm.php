<?php namespace App\Models;

class BaseIRDForm extends BaseModel {
  protected $modelName = '';
  protected $employeeModelName = '';
  protected $fillable = [
    'subject',
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

  public function employees() {
    return $this->hasMany("App\\Models\\".$this->employeeModelName, 'form_id');
  }
}