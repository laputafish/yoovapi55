<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;
use App\Models\TeamEmployee;
use App\Models\Team;
use App\Models\IrdFormFile;
use App\Models\IrdFormFileField;

use App\Helpers\TaxFormHelper;
use App\Helpers\IrdFormHelper;

class TestFormController extends Controller
{
  public function generateForm($employeeId) {
    $employee = null;
    if(\Input::has('teamId')) {
      $teamId = \Input::get('teamId');
      $team = Team::find($teamId);
      $employees = $team->employees;
      $employee = $team->employees()->whereId($employeeId)->first();
    } else {
      $employee = TeamEmployee::find($employeeId);
      $team = $employee->team;
    }

    $formCode = \Input::get('formCode', 'IR56E_PC');
    $langCode = \Input::get('langCode', 'en_us');

    $options = [];

    if(\Input::has('year')) {
      $options['year'] = \Input::get('year');
    }

    return IrdFormHelper::generate($team, $employeeId, $formCode, $langCode, $options);
//    switch($formType) {
//      case 'commencement':
//        return TaxFormHelper::generateFormCommencement($team, $employeeId, null, null, $options);
//        break;
//      case 'termination':
//        break;
//      case 'departure':
//        break;
//      case 'salary':
//        break;
//      default:
//        echo 'Form type not specified.'; nl();
//    }

  }

  public function copyTemplateFields($fromId, $toId) {
    $fromIrdFormFileId = $fromId;
    $toIrdFormFileId = $toId;

    $irdFormFile = IrdFormFile::find($fromId);
    $fields = $irdFormFile->fields;

    $targetIrdFormFile = IrdFormFile::find($toId);

    $result = [];
    foreach($fields as $field) {
      unset($field->ird_form_file_id);
      // $a = json_encode($field);
      // print_r( json_decode($a) );


      $targetIrdFormFile->fields()->save(new IrdFormFileField([
        'key' => $field['key'],
        'type' => $field['type'],
        'x' => $field['x'],
        'y' => $field['y'],
        'font_size' => $field['font_size'],
        'font-style' => $field['font_style'],
        'relative_to' => $field['relative_to'],
        'relative_to_key_id' => $field['relative_to_key_id'],
        'width' => $field['width'],
        'field_count' => $field['field_count'],
        'align' => $field['align'],
        'char_align' =>  $field['char_align'],
        'lang' => $field['lang'],
        'append_asterisk' => $field['append_asterisk'],
        'to_currency' => $field['to_currency'],
        'remark' => $field['remark']
      ]));
    }

    dd('ok');
  }

}
