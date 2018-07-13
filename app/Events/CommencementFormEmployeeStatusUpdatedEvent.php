<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommencementFormEmployeeStatusUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $statusInfo;
    /*
      'team' => $form->team,
      'formId' => $form->id,
      'employeeId' => $employee->employee_id,
      'status' => 'ready_for_processing'
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
        return new Channel( 'item_'.$this->statusInfo['team']['oa_team_id']);
        // return new PrivateChannel('channel-name');
    }

    public function broadcastAs() {
      return 'commencement_form_employee_status_updated';
    }
}
