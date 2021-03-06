<?php namespace App\Models;

class IrdForm extends BaseModel {
  protected $fillable = [
    'form_code',
    'ird_code',
    'version',
    'is_default',
    'seq_no',
    'enabled',
    'description',
    'partial_path',
    'publishable',
    'requires_fiscal_year',
    'requires_control_list',
    'can_use_softcopy'
  ];

  protected $appends = ['ird_form_type_name'];

  public function getIrdFormTypeNameAttribute(){
    return isset($this->irdFormType) ? $this->irdFormType->name : '';
  }

  public $timestamps = false;

  public function files() {
    return $this->hasMany('App\Models\IrdFormFile');
  }

  public function fields() {
    return $this->hasMany('App\Models\IrdFormFileField');
  }

  public function getFile( $langCode='en-us' ) {
    $lang = Lang::whereCode($langCode)->first();
    $irdFormFile = $this->files()->whereLangId( $lang->id )->first();
    if(is_null($irdFormFile)) {
      $irdFormFile = $this->files()->first();
    }
    return $irdFormFile;
  }

  public function irdFormType() {
    return $this->belongsTo('App\Models\IrdFormType');
  }
}