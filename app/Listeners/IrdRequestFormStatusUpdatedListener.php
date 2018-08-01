<?php

namespace App\Listeners;

use App\Events\IrdRequestFormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class IrdRequestFormStatusUpdatedListener
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
   * @param  IrdFormStatusUpdatedEvent  $event
   * @return void
   */
  public function handle(IrdRequestFormStatusUpdatedEvent $event)
  {
    //
  }
}
