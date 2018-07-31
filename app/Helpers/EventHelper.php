<?php namespace App\Helpers;

use App\Events\IrdFormStatusUpdatedEvent;
use App\Events\IrdFormEmployeeStatusUpdatedEvent;

use App\Events\IrdRequestFormStatusUpdatedEvent;
use App\Events\IrdRequestFormItemStatusUpdatedEvent;

use App\Events\xxxCommencementFormStatusUpdatedEvent;
use App\Events\xxxxxCommencementFormEmployeeStatusUpdatedEvent;

use App\Events\xxxTerminationFormStatusUpdatedEvent;
use App\Events\xxxTerminationFormEmployeeStatusUpdatedEvent;

use App\Events\xxxDepartureFormStatusUpdatedEvent;
use App\Events\xxxDepartureFormEmployeeStatusUpdatedEvent;

use App\Events\xxxSalaryFormStatusUpdatedEvent;
use App\Events\xxxSalaryFormEmployeeStatusUpdatedEvent;

class EventHelper {
  public static function send( $eventType, $options )
  {
    switch ($eventType) {
      case 'form':
        $team = $options['form']->team;
        event(new IrdFormStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['form']->id,
          'total' => $options['form']->employees()->count(),
          'progress' => 0,
          'status' => $options['form']->status
        ]));
        break;
      case 'formEmployee':
        $team = $options['form']->team;
        event(new IrdFormEmployeeStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['form']->id,
          'employeeId' => $options['formEmployee']->employee_id,
          'status' => $options['formEmployee']->status
        ]));
        break;
      case 'requestForm':
        $team = $options['sampleForm']->team;
        event(new IrdRequestFormStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['sampleForm']->id,
          'status' => $options['sampleForm']->status
        ]));
        break;
      case 'requestFormItem':
        $team = $options['sampleForm']->team;
        event(new IrdRequestFormItemStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['sampleForm']->id,
          'processed_printed_forms' => $options['sampleForm']->processed_printed_forms,
          'processed_softcopies'=> $options['sampleForm']->processed_softcopies,
          'status' => $options['sampleForm']->status
        ]));
        break;
      default:
        echo 'Unknown event!'; nl();
    }
  }
}
