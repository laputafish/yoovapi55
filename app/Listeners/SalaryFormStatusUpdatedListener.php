<?php

namespace App\Listeners;

use App\Events\xxxSalaryFormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalaryFormStatusUpdatedListener
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
     * @param  xxxSalaryFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(xxxSalaryFormStatusUpdatedEvent $event)
    {
        //
    }
}
