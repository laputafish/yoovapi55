<?php

namespace App\Listeners;

use App\Events\DepartureFormStatusUpdatedEvent;
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
     * @param  DepartureFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(DepartureFormStatusUpdatedEvent $event)
    {
        //
    }
}
