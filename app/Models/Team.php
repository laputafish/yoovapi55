<?php namespace App\Models;

class Team extends BaseModel {
  protected $fillable = [
    'oa_team_id',
    'updated_at',
    'created_at'
  ];

  public function getOrCreateJob($jobType) {
    $job = $this->jobs()->whereJobType($jobType)->first();
    if(is_null($job)) {
      $job = TeamJob::create([
        'status' => 'pending',
        'job_type' => $jobType
      ]);
      $this->jobs()->save($job);
    }
    return $job;
  }

  public function getTaxFormJob() {
    $jobType = 'tax_form';
    $job = $this->jobs()->whereJobType($jobType)->first();
    if(is_null($job)) {
      $job = TeamJob::create([
        'status' => 'idle',
        'job_type' => $jobType
      ]);
      $this->jobs()->save($job);
    }
    return $job;
  }

  public function jobs() {
    return $this->hasMany('App\Models\TeamJob');
  }

  public function taxForms() {
    return $this->hasMany( 'App\Models\TaxForm');
  }

  public function getOrCreateTaxForm($employeeId, $fiscalYear) {
    $taxForm = $this->taxForms()
      ->whereEmployeeId($employeeId)
      ->whereFiscalYear($fiscalYear)
      ->first();
    if(is_null($taxForm)) {
      $taxForm = TaxForm::create([
        'employee_id' => $employeeId,
        'fiscal_year' => $fiscalYear,
        'partial_path' => '',
        'filename' => '',
        'status' => 'pending'
      ]);
    }
    $this->taxForms()->save($taxForm);
    return $taxForm;
  }
}
