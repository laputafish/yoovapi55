<?php namespace App\Models;

class IrdFormFile extends BaseModel {
  public $autoincrement = false;

  protected $fillable = [
    'lang_id',
    'file',
    'y_offset'
  ];

  public $timestamps = false;

  public function irdForm() {
    return $this->belongsTo('App\Models\IrdForm');
  }

  public function fields() {
    return $this->hasMany('App\Models\IrdFormFileField', 'ird_form_file_id');
  }
}