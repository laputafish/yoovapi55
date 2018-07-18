<?php namespace App\Http\Controllers\ApiV2;

use App\Http\Controllers\Controller;
use App\Models\TeamEmployee;
use App\Models\Team;

use App\Helpers\TaxFormHelper;

class TestFormController extends Controller
{
  public function generateForm($employeeId) {
    $formType = \Input::get('formType');
    $employee = null;
    if(\Input::has('teamId')) {
      $teamId = \Input::get('teamId');
      $team = Team::find($teamId);
      $employee = $team->employees()->whereId($employeeId)->first;
    } else {
      $employee = TeamEmployee::find($employeeId);
      $team = $employee->team;
    }
    $options = [];
    switch($formType) {
      case 'commencement':
        $options['formCode'] = \Input::get('formCode', 'IR56E_PC');
        $options['langCode'] = \Input::get('langCode', 'en_us');
        return TaxFormHelper::generateFormCommencement($team, $employeeId, null, null, $options);
        break;
      case 'termination':
        break;
      case 'departure':
        break;
      case 'salary':
        break;
      default:
        echo 'Form type not specified.'; nl();
    }
  }


}
