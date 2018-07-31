<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TaxForm;
use App\Models\TeamJob;
use App\Models\Team;
use App\Models\FormCommencement;
use App\Models\FormTermination;
use App\Models\FormDeparture;
use App\Models\FormSalary;
use App\Models\IncomeParticular;
use App\Models\TeamIncomeParticular;

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

    $lang = \Input::get('lang');
    $team->setSetting('lang', $lang);

    $incomeParticulars = \Input::get('incomeParticulars');
    foreach($incomeParticulars as $particular) {
      $particularId = $particular['id'];
      $record = IncomeParticular::find($particularId);

      $teamIncomeParticular = TeamIncomeParticular::whereTeamId($oaTeamId)->whereIncomeParticularId($particularId)->first();
      if(is_null($teamIncomeParticular)) {
        $teamIncomeParticular = teamIncomeParticular::create([
          'team_id' => $oaTeamId
        ]);
      }

      $teamIncomeParticular->pay_type_ids = implode(',', $particular['pay_type_ids']);
      $record->teamIncomeParticulars()->save($teamIncomeParticular);
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

