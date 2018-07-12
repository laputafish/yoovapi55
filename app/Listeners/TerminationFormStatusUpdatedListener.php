<?php

namespace App\Listeners;

use App\Events\TerminationFormStatusUpdatedEvent;
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
     * @param  TerminationFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(TerminationFormStatusUpdatedEvent $event)
    {
        //
    }
}
