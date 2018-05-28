<?php namespace App\Models;

class Command extends BaseModel
{
  public $table = 'commands';

  protected $fillable = [
    'name',
    'last_checked_at'
  ];

}