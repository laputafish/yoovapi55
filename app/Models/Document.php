<?php namespace App\Models;

class Document extends BaseModel
{
  public $table = 'documents';

  public $timestamps = false;

  protected $fillable = [
    'media_id',
    'filename',
    'folder_id',
    'file_type',
    'remark'
  ];

  protected $appends = [
    'occupied_by_user'
  ];

  public function getOccupiedByUserAttribute() {
    return $this->belongsTo('App\User', 'occupied_by');
  }
}