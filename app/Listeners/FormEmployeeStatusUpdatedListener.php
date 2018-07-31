<?php

namespace App\Listeners;

use App\Events\IrdFormEmployeeStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FormEmployeeStatusUpdatedListener
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
   * @param  IrdFormEmployeeStatusUpdatedEvent  $event
   * @return void
   */
  public function handle(IrdFormEmployeeStatusUpdatedEvent $event)
  {
    //
  }
}
