<?php namespace App\Models;

class Equipment extends BaseModel
{
    public $table = 'equipments';

    protected $fillable = [
        'name',
        'description',
        'occupied_by',
        'last_occupied_at',
        'remark'
    ];

    protected $appends = [
        'occupied_by_user'
    ];

    public function getOccupiedByUserAttribute() {
      return isset($this->user) ? $this->user : null;
    }

    public function user() {
        return $this->belongsTo('App\User', 'occupied_by');
    }
}