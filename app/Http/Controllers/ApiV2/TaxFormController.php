<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TaxForm;
use App\Models\TeamJob;
use App\Models\Team;
use App\Models\FormCommencement;
use App\Models\FormTermination;
use App\Models\FormDeparture;
use App\Models\FormSalary;
use App\Models\Ir56bIncome;
use App\Models\Ir56fIncome;
use App\Models\TeamIr56bIncome;
use App\Models\TeamIr56fIncome;

use App\Helpers\TeamHelper;
use App\Helpers\TeamJobHelper;
use App\Helpers\OA\OAHelper;
use App\Helpers\EventHelper;

use App\Events\xxxTaxFormNewJobEvent;
use App\Events\xxxTaxFormStatusUpdatedEvent;

class TaxFormController extends BaseAuthController
{
  protected $modelName = 'TaxForm';

  public function index()
  {
    $oaTeamId = \Input::get('teamId');
    $team = Team::whereOaTeamId($oaTeamId)->first();

    $fiscalYear = \Input::get('fiscalYear');
    $rows = $team->taxForms()->whereFiscalYear((int)$fiscalYear)->get();
    return response()->json([
      'status' => true,
      'result' => $rows
    ]);
  }

  public function store()
  {
    OAHelper::refreshTeamToken($this->user, $this->team);
    $command = \Input::get('command');
    $newStatus = '';
    switch ($command) {
      case 'update_settings':
        return $this->updateSettings();
      case 'generate':
        $newStatus = 'ready_for_processing';
        break;
      case 'terminate':
        $newStatus = 'terminated';
        break;
    }
    $formType = \Input::get('formType','');
    $formId = \Input::get('formId');
    $form = null;
    switch ($formType) {
      case 'commencements':
        $form = FormCommencement::find($formId);
        break;
      case 'terminations':
        $form = FormTermination::find($formId);
        break;
      case 'salaries':
        $form = FormSalary::find($formId);
        break;
      case 'departures':
        $form = FormDeparture::find($formId);
        break;
    }
    if(!is_null($form)) {
      $update = ['status'=>$newStatus];
      $form->update($update);
      $form->employees()->update($update);
// dd($form);
      EventHelper::send('commencementForm', ['form'=>$form]);

      foreach($form->employees as $formEmployee) {
//        echo 'employee_id = '.$formEmployee['employee_id']; nl();
//        dd($formEmployee);
        EventHelper::send('commencementFormEmployee', [
          'form'=>$form,
          'formEmployee'=>$formEmployee]);
      }
    }

    return response()->json([
      'status'=>true,
      'result'=>$newStatus
    ]);
  }

  public function updateSettings() {
    $oaTeamId = \Input::get('teamId');
    $team = Team::whereOaTeamId($oaTeamId)->first();

    $team->setSetting('lang', \Input::get('lang', 'en-us'));
    $team->setSetting('designation', \Input::get('designation', ''));
    $team->setSetting('signatureName', \Input::get('signatureName', ''));

    // IR56B Income Mapping
    $ir56bIncomes = \Input::get('ir56bIncomes');
    foreach($ir56bIncomes as $ir56bIncome) {
      $particularId = $ir56bIncome['id'];
      $record = Ir56bIncome::find($particularId);

      $teamIr56bIncome = TeamIr56bIncome::whereTeamId($oaTeamId)->whereIr56bIncomeId($particularId)->first();
      if(is_null($teamIr56bIncome)) {
        $teamIr56bIncome = TeamIr56bIncome::create([
          'team_id' => $oaTeamId
        ]);
      }
      $teamIr56bIncome->pay_type_ids = implode(',', $ir56bIncome['pay_type_ids']);
      $record->teamIr56bIncomes()->save($teamIr56bIncome);
    }

    // IR56F Income Mapping
    $ir56fIncomes = \Input::get('ir56fIncomes');
    foreach($ir56fIncomes as $ir56fIncome) {
      $particularId = $ir56fIncome['id'];
      $record = Ir56fIncome::find($particularId);

      $teamIr56fIncome = TeamIr56fIncome::whereTeamId($oaTeamId)->whereIr56fIncomeId($particularId)->first();
      if(is_null($teamIr56fIncome)) {
        $teamIr56fIncome = TeamIr56fIncome::create([
          'team_id' => $oaTeamId
        ]);
      }
      $teamIr56fIncome->pay_type_ids = implode(',', $ir56fIncome['pay_type_ids']);
      $record->teamIr56fIncomes()->save($teamIr56fIncome);
    }

    return response()->json([
      'status'=>true
    ]);
  }

  public function generate($form) {
    if(is_a($form, 'App\Models\FormSalary')) {

    }
    $user = app('auth')->guard()->user();
  }

  public function generatexxx() {
    $user = app('auth')->guard()->user();

    $oaTeamId = \Input::get('teamId');
    $fiscalYear = (int) \Input::get('fiscalYear');
    $employeeIds = \Input::get('employeeIds');

    $team = TeamHelper::getOrCreate($oaTeamId);

    $job = $team->getOrCreateJob('tax_form');
    $job = TeamJob::find($job->id);
    $job->oa_access_token = $user->oa_access_token;
    $job->oa_token_type = $user->oa_token_type;
    $job->save();

    $job->items()->update([
      'enabled'=>0
    ]);
    foreach($employeeIds as $employeeIdStr ) {
      $employeeId = (int) $employeeIdStr;
      // for summary notification
      $item = $job->getOrCreateItem($employeeId); // $job->getOrCreateItem($employeeId);
      $item->enabled = 1;
      $item->save();

      $taxForm = $team->getOrCreateTaxForm($employeeId, $fiscalYear);
      $taxForm->status = 'pending';
      $taxForm->save();
//      event(new TaxFormNewItemEvent($taxForm));
    }

    // set status = 'pending', trigger action to start in background
    $job->status = 'pending';
    $job->fiscal_year = (int) $fiscalYear;
    $job->save();

    $total = $job->items()->whereEnabled(0)->count();
    $job->team = $job->team;

    event(new xxxTaxFormNewJobEvent($job));

    return response()->json([
      'status'=>true,
      'job'=>$job,
      'total'=>$total
    ]);
  }
}

