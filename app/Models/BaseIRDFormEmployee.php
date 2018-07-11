<?php namespace App\Models;

use App\Helpers\TaxFormHelper;

class BaseIRDFormEmployee extends BaseModel {
  protected $formType = 'unknown';
  protected $parentModelName = '';

  public $timestamps = false;

  protected $fillable = [
    'form_id',
    'employee_id',
    'file',
    'status'
  ];

  public function form() {
    return $this->belongsTo("App\\Models\\$this->parentModelName", 'form_id');
  }

}