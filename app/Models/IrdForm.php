<?php namespace App\Models;

class IrdForm extends BaseModel {
  protected $fillable = [
    'form_code',
    'description',
    'partial_path'
  ];
}