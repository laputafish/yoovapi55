<?php namespace App\Models;

class Team extends BaseModel {
  protected $fillable = [
    'oa_team_id',
    'oa_access_token',
    'oa_token_type',

    'oa_team_code',
    'oa_team_currency_code',
    'oa_access_token',
    'oa_team_logo_path',
    'oa_team_name',
    'oa_team_created_at',

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

  public function commencementForms() {
    return $this->hasMany('App\Models\FormCommencement');
  }

  public function departureForms() {
    return $this->hasMany('App\Models\FormDeparture');
  }

  public function terminationForms() {
    return $this->hasMany('App\Models\FormTermination');
  }

  public function salaryForms() {
    return $this->hasMany('App\Models\FormSalary');
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

  public function Settings() {
    return $this->hasMany('App\Models\TeamSetting');
  }

  public function setSetting($key, $value) {
    $setting = $this->settings()->where('key',$key)->first();
    if(isset($setting)) {
      $setting->value = $value;
      $setting->save();
    } else {
      $setting = new TeamSetting;
      $setting->key = $key;
      $setting->value = $value;
      $this->settings()->save($setting);
    }
  }
  public function getSetting($key, $default) {
    $setting = $this->settings()->where('key',$key)->first();
    $result = $default;
    if(isset($setting)) {
      $result = $setting->value;
    }
    return $result;
  }

  public function getOaAuth() {
    return [
      'oa_token_type'=>$this->oa_token_type,
      'oa_access_token'=>$this->oa_access_token
    ];
  }

  public function teamIr56bIncomes() {
    return $this->hasMany('App\Models\TeamIr56bIncome', 'team_id', 'oa_team_id');
  }

  public function teamIr56fIncomes() {
    return $this->hasMany('App\Models\TeamIr56fIncome', 'team_id', 'oa_team_id');
  }

  public function teamIr56mIncomes() {
    return $this->hasMany('App\Models\TeamIr56mIncome', 'team_id', 'oa_team_id');
  }


//  public function incomeParticulars() {
//    return $this->hasMany('App\Models\TeamIr56bIncome', 'team_id', 'oa_team_id');
//  }

  public function employees() {
    return $this->hasMany('App\Models\TeamEmployee');
  }

  public function sampleForm() {
    return $this->hasOne('App\Models\SampleForm');
  }
}
