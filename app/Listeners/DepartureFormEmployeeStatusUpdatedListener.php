<?php

namespace App\Listeners;

use App\Events\DepartureFormEmployeeStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DepartureFormEmployeeStatusUpdatedListener
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
     * @param  DepartureFormEmployeeStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(DepartureFormEmployeeStatusUpdatedEvent $event)
    {
        //
    }
}
