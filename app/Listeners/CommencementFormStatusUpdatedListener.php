<?php

namespace App\Listeners;

use App\Events\xxxCommencementFormStatusUpdatedEvent;
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
     * @param  xxxCommencementFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(xxxCommencementFormStatusUpdatedEvent $event)
    {
        //
    }
}
