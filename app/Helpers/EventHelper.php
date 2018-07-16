<?php namespace App\Helpers;

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
      case 'commencementForm':
        event(new CommencementFormStatusUpdatedEvent([
          'team' => isset($team) ? $team->toArray() : null,
          'formId' => $options['form']->id,
          'total' => $options['form']->employees()->count(),
          'progress' => 0,
          'status' => $options['form']->status
        ]));
        break;
      case 'commencementFormEmployee':
//        echo 'commencementFormEmployee event  employee id = '
//          .$options['formEmployee']->employee_id
//          .'  status = '
//          .$options['formEmployee']->status; nl();
        event(new CommencementFormEmployeeStatusUpdatedEvent([
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