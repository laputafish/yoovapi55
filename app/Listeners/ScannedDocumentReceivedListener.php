<?php

namespace App\Listeners;

use App\Events\ScannedDocumentReceivedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ScannedDocumentReceivedListener
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

    public function onDocumentAdded($event) {
      broadcast( new ScannedDocumentReceived($document));
    }
    /**
     * Handle the event.
     *
     * @param  ScannedDocumentReceivedEvent  $event
     * @return void
     */
    public function handle(ScannedDocumentReceivedEvent $event)
    {
        //
    }

    public function subscribe($events)
    {
      $events.listen(
        'App\Events\ScannedDocumentReceivedEvent',
        'App\Listeners\ScannedDocumentReceivedListener@onDocumentAdded'
      );
    }
}
