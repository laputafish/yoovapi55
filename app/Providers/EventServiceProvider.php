<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
      'App\Events\ScannedDocumentReceivedEvent' => [
        'App\Listeners\ScannedDocumentReceivedListener'
      ],
      'App\Events\TaxFormNewJobEvent' => [
        'App\Listeners\TaxFormNewJobListener'
      ],
      'App\Events\TaxFormStatusUpdatedEvent' => [
        'App\Listeners\TaxFormStatusUpdatedListener'
      ],
      // Form
      'App\Events\FormStatusUpdatedEvent' => [
        'App\Listeners\FormStatusUpdatedListener',
      ],
      'App\Events\FormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\FormEmployeeStatusUpdatedListener',
      ],
      // Commencement Form
      'App\Events\CommencementFormStatusUpdatedEvent' => [
        'App\Listeners\CommencementFormStatusUpdatedListener',
      ],
      'App\Events\CommencementFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\CommencementFormEmployeeStatusUpdatedListener',
      ],
      // Termination Form
      'App\Events\TerminationFormStatusUpdatedEvent' => [
        'App\Listeners\TerminationFormStatusUpdatedListener',
      ],
      'App\Events\TerminationFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\TerminationFormEmployeeStatusUpdatedListener',
      ],
      // Departure Form
      'App\Events\DepartureFormStatusUpdatedEvent' => [
        'App\Listeners\DepartureFormStatusUpdatedListener',
      ],
      'App\Events\DepartureFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\DepartureFormEmployeeStatusUpdatedListener',
      ],
      // Salary Form
      'App\Events\SalaryFormStatusUpdatedEvent' => [
        'App\Listeners\SalaryFormStatusUpdatedListener',
      ],
      'App\Events\SalaryFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\SalaryFormEmployeeStatusUpdatedListener',
      ]

//      'App\Events\Event' => [
//        'App\Listeners\EventListener',
//      ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

    }
}
