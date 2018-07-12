<?php

namespace App\Listeners;

use App\Events\SalaryFormStatusUpdatedEvent;
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
     * @param  SalaryFormStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(SalaryFormStatusUpdatedEvent $event)
    {
        //
    }
}
