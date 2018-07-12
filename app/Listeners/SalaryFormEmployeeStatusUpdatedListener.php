<?php

namespace App\Listeners;

use App\Events\SalaryFormEmployeeStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalaryFormEmployeeStatusUpdatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SalaryFormEmployeeStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(SalaryFormEmployeeStatusUpdatedEvent $event)
    {
        //
    }
}
