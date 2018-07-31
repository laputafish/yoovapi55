<?php

namespace App\Listeners;

use App\Events\xxxTaxFormStatusUpdatedEvent;
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
     * @param  xxxTaxFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(xxxTaxFormStatusUpdatedEvent $event)
    {
        //
    }
}
