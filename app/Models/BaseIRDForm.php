<?php namespace App\Models;

class BaseIRDForm extends BaseModel {
  protected $modelName = '';
  protected $employeeModelName = '';
  protected $fillable = [
    'team_id',
    'form_no',
    'form_date',
    'status',
    'subject',
    'ird_form_id',
    'remark',
    'submitted_on'
  ];

  protected $appends = [
    'employee_count'
  ];

  public function getEmployeeCountAttribute() {
    return $this->employees()->count();
  }

  public function lang() {
    return $this->belongsTo('App\Models\Lang' );
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

  public function getTemplateFilePathAttribute() {
    $className = get_class($this);
    switch( $className ) {
      case 'App\Models\FormCommencement':

        break;
      case 'App\Models\FormTermination':
        break;
      case 'App\Models\FormDeparture':
        break;
      case 'App\Models\FormSalary':
        break;
    }
  }

}