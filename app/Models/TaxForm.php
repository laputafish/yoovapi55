<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxForm extends Model
{
    protected $fillable = [
      'employee_id',
      'team_id',
      'fiscal_year',
      'partial_path',
      'filename',
      'status'
    ];

    public function user() {
      return $this->belongsTo('App\Models\User');
    }
}
