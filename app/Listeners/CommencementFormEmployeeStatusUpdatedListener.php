<?php

namespace App\Listeners;

use App\Events\CommencementFormEmployeeStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommencementFormEmployeeStatusUpdatedListener
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
     * @param  CommencementFormEmployeeStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(CommencementFormEmployeeStatusUpdatedEvent $event)
    {
        //
    }
}
