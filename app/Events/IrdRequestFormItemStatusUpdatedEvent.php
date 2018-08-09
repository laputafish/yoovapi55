<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class IrdRequestFormItemStatusUpdatedEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $statusInfo;
  /*
    'team' => isset($team) ? $team->toArray() : null,
    'formId' => $options['sampleForm']->id,
    'processed_printed_forms' => $options['sampleForm']->processed_printed_forms,
    'processed_softcopies'=> $options['sampleForm']->processed_softcopies,
    'status' => $options['sampleForm']->status
*/
  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($statusInfo)
  {
    $this->statusInfo = $statusInfo;
//    echo 'formEmployee event created: ';
//    nl();
//    echo 'oa_team_id = ' . $this->statusInfo['team']['oa_team_id'];
//    nl();
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new Channel('team_' . $this->statusInfo['team']['oa_team_id']);
    // return new PrivateChannel('channel-name');
  }

  public function broadcastAs()
  {
    return 'ird_request_form_item_status_updated';
  }
}
