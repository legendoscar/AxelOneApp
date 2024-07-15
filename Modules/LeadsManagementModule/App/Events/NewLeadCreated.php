<?php

namespace Modules\LeadsManagementModule\App\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class NewLeadCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lead, $payload;

    public function __construct($lead, $payload)
    {
        $this->lead = $lead;
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return new Channel('leads');
    }

    public function broadcastWith()
    {
        return [
            'lead_id' => $this->lead->id,
            'message' => 'A new lead is available.',
            'searchTerm' => $this->payload['searchTerm'],
            'searchFields' => $this->payload['searchFields'],
            'lead_partial_info' => $this->getPartialLeadInfo(),
        ];
    }
    protected function getPartialLeadInfo()
    {
        return [
            'name' => $this->lead->firstname . ' ' . $this->lead->lastname,
            'email' => '**********', // Hide email
            'phone' => '**********', // Hide phone
        ];
    }
}
