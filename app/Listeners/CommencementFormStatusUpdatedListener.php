<?php

namespace App\Listeners;

use App\Events\CommencementFormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommencementFormStatusUpdatedListener
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
     * @param  CommencementFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(CommencementFormStatusUpdatedEvent $event)
    {
        //
    }
}
