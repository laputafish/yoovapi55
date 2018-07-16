<?php namespace App\Models;

class IrdForm extends BaseModel {
  protected $fillable = [
    'form_code',
    'description',
    'partial_path'
  ];

  public $timestamps = false;

  public function files() {
    return $this->hasMany('App\Models\IrdFormFile');
  }

  public function fields() {
    return $this->hasMany( 'App\Models\IrdFormField');
  }
}