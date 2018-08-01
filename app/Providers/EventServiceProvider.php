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
      'App\Events\xxxTaxFormNewJobEvent' => [
        'App\Listeners\TaxFormNewJobListener'
      ],
      'App\Events\xxxTaxFormStatusUpdatedEvent' => [
        'App\Listeners\TaxFormStatusUpdatedListener'
      ],
      // Form
      'App\Events\IrdFormStatusUpdatedEvent' => [
        'App\Listeners\IrdFormStatusUpdatedListener',
      ],
      'App\Events\IrdFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\IrdFormEmployeeStatusUpdatedListener',
      ],
      // Reqeust Form
      'App\Events\IrdRequestFormStatusUpdatedEvent' => [
        'App\Listeners\IrdRequestFormStatusUpdatedListener',
      ],
      'App\Events\IrdRequestFormItemStatusUpdatedEvent' => [
        'App\Listeners\IrdRequestFormItemStatusUpdatedListener',
      ],
      // Commencement Form
      'App\Events\xxxCommencementFormStatusUpdatedEvent' => [
        'App\Listeners\CommencementFormStatusUpdatedListener',
      ],
      'App\Events\xxxxxCommencementFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\CommencementFormEmployeeStatusUpdatedListener',
      ],
      // Termination Form
      'App\Events\xxxTerminationFormStatusUpdatedEvent' => [
        'App\Listeners\TerminationFormStatusUpdatedListener',
      ],
      'App\Events\xxxTerminationFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\TerminationFormEmployeeStatusUpdatedListener',
      ],
      // Departure Form
      'App\Events\xxxDepartureFormStatusUpdatedEvent' => [
        'App\Listeners\DepartureFormStatusUpdatedListener',
      ],
      'App\Events\xxxDepartureFormEmployeeStatusUpdatedEvent' => [
        'App\Listeners\DepartureFormEmployeeStatusUpdatedListener',
      ],
      // Salary Form
      'App\Events\xxxSalaryFormStatusUpdatedEvent' => [
        'App\Listeners\SalaryFormStatusUpdatedListener',
      ],
      'App\Events\xxxSalaryFormEmployeeStatusUpdatedEvent' => [
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
