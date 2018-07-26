<?php namespace App\Models\SampleEmployee;

class SampleEmployee extends BaseModel {
  public $timestamps = false;

  protected $fillable = [
    'surname',
    'given_name',
    'gender'
  ];
}