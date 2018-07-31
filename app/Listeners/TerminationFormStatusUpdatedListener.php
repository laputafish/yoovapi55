<?php

namespace App\Listeners;

use App\Events\xxxTerminationFormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TerminationFormStatusUpdatedListener
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
     * @param  xxxTerminationFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(xxxTerminationFormStatusUpdatedEvent $event)
    {
        //
    }
}
