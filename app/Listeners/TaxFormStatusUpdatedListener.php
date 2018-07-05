<?php

namespace App\Listeners;

use App\Events\TaxFormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaxFormStatusUpdatedListener
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
     * @param  TaxFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(TaxFormStatusUpdatedEvent $event)
    {
        //
    }
}
