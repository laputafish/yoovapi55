<?php

namespace App\Listeners;

use App\Events\FormStatusUpdatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FormStatusUpdatedListener
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
   * @param  FormStatusUpdatedEvent  $event
   * @return void
   */
  public function handle(FormStatusUpdatedEvent $event)
  {
    //
  }
}
