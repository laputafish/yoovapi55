<?php namespace App\Models;

class IrdFormFile extends BaseModel {
  public $autoincrement = false;

  protected $fillable = [
    'lang_id',
    'file'
  ];

  public $timestamps = false;

  public function irdForm() {
    return $this->belongsTo('App\Models\IrdForm');
  }

}