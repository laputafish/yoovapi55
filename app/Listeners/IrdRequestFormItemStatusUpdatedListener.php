<?php

namespace App\Listeners;

use App\Events\IrdRequestFormItemStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class IrdRequestFormItemStatusUpdatedListener
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
  public function handle(IrdRequestFormItemStatusUpdatedEvent $event)
  {
    //
  }
}
