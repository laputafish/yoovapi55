<?php namespace App\Helpers;

use App\Events\FormStatusUpdatedEvent;
use App\Events\FormEmployeeStatusUpdatedEvent;

use App\Events\CommencementFormStatusUpdatedEvent;
use App\Events\CommencementFormEmployeeStatusUpdatedEvent;

use App\Events\TerminationFormStatusUpdatedEvent;
use App\Events\TerminationFormEmployeeStatusUpdatedEvent;

use App\Events\DepartureFormStatusUpdatedEvent;
use App\Events\DepartureFormEmployeeStatusUpdatedEvent;

use App\Events\SalaryFormStatusUpdatedEvent;
use App\Events\SalaryFormEmployeeStatusUpdatedEvent;

class EventHelper {
  public static function send( $eventType, $options )
  {
    $team = $options['form']->team;
    switch ($eventType) {
      case 'form':
        event(new FormStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['form']->id,
          'total' => $options['form']->employees()->count(),
          'progress' => 0,
          'status' => $options['form']->status
        ]));
        break;
      case 'formEmployee':
        event(new FormEmployeeStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['form']->id,
          'employeeId' => $options['formEmployee']->employee_id,
          'status' => $options['formEmployee']->status
        ]));
        break;
      default:
        echo 'Unknown event!'; nl();
    }
  }
}