<?php namespace App\Models;

class IrdFormField extends BaseModel {
  protected $fillable = [
    'ird_form_id',
    'key',
    'type',
    'x',
    'y',
    'font_size',
    'relative_to',
    'relative_to_key_id',
    'width',
    'field_count',
    'align',
    'char_align',
    'lang',
    'remark'
  ];

  public $timestamps = false;

  public function irdForm() {
    return $this->belongsTo('App\Models\IrdForm');
  }
}