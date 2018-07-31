<?php

namespace App\Listeners;

use App\Events\xxxDepartureFormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DepartureFormStatusUpdatedListener
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
     * @param  xxxDepartureFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(xxxDepartureFormStatusUpdatedEvent $event)
    {
        //
    }
}
