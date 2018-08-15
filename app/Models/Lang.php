<?php namespace App\Models;

class Lang extends BaseModel {
  protected $fillable = [
    'code',
    'name',
    'token',
    'oa_lang_id',
    'locale',
    'label_tag',
    'enabled',
    'default'
  ];
}