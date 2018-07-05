<?php

namespace App\Listeners;

use App\Events\TaxFormNewItemEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaxFormNewItemListener
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
     * @param  TaxFormNewItemEvent  $event
     * @return void
     */
    public function handle(TaxFormNewItemEvent $event)
    {
        //
    }
}
