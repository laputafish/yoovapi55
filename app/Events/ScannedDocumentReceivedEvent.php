<?php

namespace App\Events;

use App\Models\Document;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ScannedDocumentReceivedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $document;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($document)
    {
      $this->document = $document;
      echo "ScannedDocumentReceived event created\n";
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        echo "broadcastOn\n";
        //return new PrivateChannel('channel-name');
        return new Channel('documents');
    }

    public function broadcastAs()
    {
      echo "broadcastAs\n";
      return 'document_added';
    }
}
