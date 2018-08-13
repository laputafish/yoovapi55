<?php namespace App\Models;

class TempFile extends BaseModel {
  protected $fillable = [
    'key',
    'label',
    'filename',
    'user_id',
    'created_at',
    'updated_at'
  ];
}