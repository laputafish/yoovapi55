<?php namespace App\Models;

class IrdFormFileField extends BaseModel {
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
    'append_asterisk',
    'to_currency',
    'remark'
  ];

  public $timestamps = false;

  public function irdFormFile() {
    return $this->belongsTo('App\Models\IrdFormFile', 'ird_form_file_id');
  }
}