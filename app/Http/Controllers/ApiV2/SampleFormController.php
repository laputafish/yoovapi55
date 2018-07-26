<?php namespace App\Http\Controllers\ApiV2;

use App\Helpers\TaxFormHelper;
use App\Helpers\EventHelper;
use App\Helpers\OA\OAHelper;
use App\Helpers\FormHelper;

use App\Models\FormEmployee;

class SampleFormController extends BaseAuthController
{
  protected $modelName = 'SampleForm';


  public function store() {
    $command = \Input::get('command', '');
    switch($command) {
      case 'generate':
        $this->generateSampleForm();
        break;
    }
    return response()->json([
      'stauts'=>true
    ]);
  }

  public function generateSampleForm() {
    $irdFormId = \Input::get('irdFormId', '');
    if(empty($irdFormId)) {
      $formCode = \Input::get('formCode', 'IR56B');
      $irdForm = IrdForm::whereFormCode(strtoupper($formCode))->first();
    } else {
      $irdForm = IrdForm::find($irdFormId);
    }

    $sampleForm = $this->team->sampleForms()->whereIrdFormId($irdForm->id)->first();
    if(is_null($sampleForm)) {
      $sampleForm = $this->team->sampleForms()->save(new SampleForm([
        'lang_id'=>\Input::get('lang_id'),
        'form_date'=>\Input::get('form_date'),
        'status'=>'pending',
        'ird_form_type_id'=>$irdForm->ird_form_type_id,
        'fiscal_start_year'=>getCurrentFiscalYearStartYear(),
        'remark'=>'',
        'designation'=>\Input::get('designation','Manager')
      ]));
    }
    dd($sampleForm->toArray());

  }
}
