<?php namespace App\Models;

class Lang extends BaseModel {
  protected $fillable = [
    'code',
    'name',
    'token',
    'label_tag',
    'enabled'
  ];
}