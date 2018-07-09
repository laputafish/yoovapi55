<?php namespace App\Models;

class BaseIRDFormEmployee extends BaseModel {
  protected $parentModelName = '';

  protected $fillable = [
    'form_id',
    'employee_id'
  ];

  public function form() {
    return $this->belongsTo("App\\Models\\$this->parentModelName", 'form_id');
  }
}