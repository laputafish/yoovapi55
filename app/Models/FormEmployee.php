<?php namespace App\Models;

class FormEmployee extends BaseModel {
  public $incrementing = false;
  public $timestamps = false;
  protected $fillable = [
    'form_id',
    'sheet_no',
    'employee_id',
    'file',
    'status'
  ];

  public function form() {
    return $this->belongsTo('App\Models\Form', 'form_id');
  }

}