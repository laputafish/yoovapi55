<?php namespace App\Models;

class Form extends BaseIRDForm {
  protected $modelName = 'Form';
  protected $employeeModelName = 'FormEmployee';

  protected $fillable = [
    'team_id',
    'form_no',
    'form_date',
    'lang_id',
    'status',
    'subject',
    'ird_form_type_id',
    'ird_form_id',
    'fiscal_start_year',
    'remark',
    'signature_name',
    'designation'
  ];

  public function irdFormType() {
    return $this->belongsTo('App\Models\IrdFormType');
  }

  public function irdForm() {
    return $this->belongsTo( 'App\Models\IrdForm');
  }

}