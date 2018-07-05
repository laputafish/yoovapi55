<?php namespace App\Http\Controllers\ApiV2;

use App\Models\TaxForm;
use App\Models\TeamJob;

use App\Helpers\TeamHelper;

use App\Events\TaxFormNewJobEvent;
use App\Events\TaxFormNewItemEvent;

class TaxFormController extends BaseController
{
  protected $modelName = 'TaxForm';

  public function index()
  {
    $teamId = \Input::get('teamId');
    $fiscalYear = \Input::get('fiscalYear');
    $rows = $this->model->whereFiscalYear((int)$fiscalYear)->whereTeamId($teamId)->get();
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
        $this->generate();
        break;
    }
  }

  public function generate() {
    $oaTeamId = \Input::get('teamId');
    $fiscalYear = (int) \Input::get('fiscalYear');
    $employeeIds = \Input::get('employeeIds');

    $team = TeamHelper::getOrCreate($oaTeamId);

    $job = $team->getOrCreateJob('tax_form');
    foreach($employeeIds as $employeeId ) {
      // for summary notification
      $job->getOrCreateItem($employeeId);

      $taxForm = $team->getOrCreateTaxForm($employeeId, $fiscalYear);
      $taxForm->status = 'pending';
      $taxForm->save();

      event(new TaxFormNewItemEvent($taxForm));
    }

    // set status = 'pending', trigger action to start in background
    $job->status = 'pending';
    $job->fiscal_year = (int) $fiscalYear;
    $job->save();

    $job->items = $job->items;
    event(new TaxFormNewJobEvent($job));

    return response()->json([
      'status'=>true
    ]);
  }
}

