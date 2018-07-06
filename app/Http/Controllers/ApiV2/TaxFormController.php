<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TaxForm;
use App\Models\TeamJob;
use App\Models\Team;

use App\Helpers\TeamHelper;
use App\Helpers\TeamJobHelper;

use App\Events\TaxFormNewJobEvent;
use App\Events\TaxFormStatusUpdatedEvent;

class TaxFormController extends BaseController
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
    $command = \Input::get('command');
    switch ($command) {
      case 'generate':
        return $this->generate();
        break;
    }
  }

  public function generate() {
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

    event(new TaxFormNewJobEvent($job));

    return response()->json([
      'status'=>true,
      'job'=>$job,
      'total'=>$total
    ]);
  }
}

