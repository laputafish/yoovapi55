<?php namespace App\Helpers;

use Illuminate\Support\Carbon;
use App\Models\Command;
use App\Models\TeamJob;

use App\Events\TaxFormStatusUpdatedEvent;

class TaxFormHelper
{
  public static function checkPending()
  {
    \Carbon\Carbon::setLocale(config('app.locale'));

    $command = Command::whereName('generateTaxForms')->first();
    if(is_null($command)) {
      $command = Command::create([
        'name' => 'generateTaxForms',
        'enabled'=>1
      ]);
    }

    if(!$command->enabled) {
        return;
    }

    if(isset($command->last_checked_at)) {
      $now = now();
      $lastCheckedAt = Carbon::parse($command->last_checked_at);
      $durationPassed = $now->diffInSeconds($lastCheckedAt);
      if ($durationPassed < 60 && ($command->mode == 'auto')) {
        echo "duration since last checking < 60sec => quit\n";
        return;
      }
    }

    // Process
    while(true) {
      $now = now();
      $command->last_checked_at = $now;
      $command->save();
      
      $teamJobs = TeamJob::whereStatus('pending')->get();
      foreach ($teamJobs as $job) {
        $team = $job->team;
        $fiscalYear = $job->fiscal_year;

        $jobItems = $job->items;
        $totalCount = $jobItems->count();

        foreach ($jobItems as $i => $item) {
          $employeeId = $item->employee_id;

          $taxForm = $team->getOrCreateTaxForm($employeeId, $fiscalYear);
          if ($taxForm->status == 'pending') {
            $taxForm->status = 'processing';
            $taxForm->save();
            event(new TaxFormStatusUpdatedEvent([
              'team' => $team,
              'index' => $i,
              'item' => $item,
              'total' => $totalCount
            ]));
            //*******************
            // Generation
            //*******************
            // self::generateTaxForm($taxForm);
            sleep(2);
            $taxForm->status = 'ready';
            $taxForm->save();
            event(new TaxFormStatusUpdatedEvent([
              'team' => $team,
              'index' => $i,
              'item' => $item,
              'total' => $totalCount
            ]));
          }
        }
      }

      sleep(1);

      // Check enabled
      $command = Command::whereName('generateTaxForms')->first();
      if(!$command->enabled) {
        echo "command not enabled => quit\n";
        break;
      }

      if($command->mode == 'manual') {
        echo "command mode = manual => after run once => quit\n";
        break;
      }

    }
  }
}