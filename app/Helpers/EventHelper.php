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
    switch ($eventType) {
      case 'commencementForm':
        event(new CommencementFormStatusUpdatedEvent([
          'team' => $options['form']->team->toArray(),
          'formId' => $options['form']->id,
          'total' => $options['form']->employees()->count(),
          'progress' => 0,
          'status' => $options['form']->status
        ]));
        break;
      case 'commencementFormEmployee':
        event(new CommencementFormEmployeeStatusUpdatedEvent([
          'team' => $options['form']->team->toArray(),
          'formId' => $options['form']->id,
          'employeeId' => $options['formEmployee']['employee_id'],
          'status' => $options['form']->status
        ]));
        break;
    }
  }
}