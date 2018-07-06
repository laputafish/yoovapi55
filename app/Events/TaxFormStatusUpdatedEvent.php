<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TaxFormStatusUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $statusInfo;
    /*
    public $team;
    public $index;
    public $taxForm;
    public $total;
    */

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($statusInfo)
    {
      $this->statusInfo = $statusInfo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
      return new Channel('team_'.$this->statusInfo['team']->oa_team_id );
    }

    public function broadcastAs() {
      return 'tax_form_status_updated';
    }
}
