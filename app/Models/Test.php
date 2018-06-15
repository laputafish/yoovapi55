<?php namespace App\Models;

class Test extends BaseModel {
  protected $table = 'tests';
  protected $fillable = ['user_id'];

  public $timestamps = false;
}
