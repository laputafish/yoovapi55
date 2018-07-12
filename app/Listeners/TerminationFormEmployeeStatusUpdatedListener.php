<?php

namespace App\Listeners;

use App\Events\TerminationFormEmployeeStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TerminationFormEmployeeStatusUpdatedListener
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
     * @param  TerminationFormEmployeeStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(TerminationFormEmployeeStatusUpdatedEvent $event)
    {
        //
    }
}
