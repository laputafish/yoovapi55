<?php namespace App\Models;

class SampleForm extends BaseIRDForm {
  protected $modelName = 'SampleForm';
  protected $employeeModelName = 'SampleFormEmployee';

  protected $fillable = [
    'team_id',
    'lang_id',
    'application_date',
    'apply_printed_forms',
    'apply_softcopies',
    'company_file_no',
    'company_name',
    'tel_no',
    'signature_name',
    'designation',
    'fiscal_start_year',
    'fiscal_years',
    'is_update',
    'remark'
  ];

  public function irdFormType() {
    return $this->belongsTo('App\Models\IrdFormType');
  }

  public function irdForm() {
    return $this->belongsTo( 'App\Models\IrdForm');
  }

}