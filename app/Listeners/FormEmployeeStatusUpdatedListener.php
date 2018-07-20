<?php

namespace App\Listeners;

use App\Events\FormEmployeeStatusUpdatedEvent;
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
   * @param  FormEmployeeStatusUpdatedEvent  $event
   * @return void
   */
  public function handle(FormEmployeeStatusUpdatedEvent $event)
  {
    //
  }
}
