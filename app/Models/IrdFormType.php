<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrdFormType extends Model
{
    protected $fillable = [
      'name'
    ];

    public function forms() {
      return $this->hasMany('App\Models\IrdForm');
    }
}
