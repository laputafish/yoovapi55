<?php namespace App\Models;

class IrdFormFile extends BaseModel {
  public $autoincrement = false;

  protected $fillable = [
    'field_name',
    'ir_field_name'
  ];

  public $timestamps = false;

}