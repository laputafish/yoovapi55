<?php

namespace App\Listeners;

use App\Events\TaxFormNewJobEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaxFormNewJobListener
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
     * @param  TaxFormNewJobEvent  $event
     * @return void
     */
    public function handle(TaxFormNewJobEvent $event)
    {
        //
    }
}
