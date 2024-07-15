<?php

namespace Modules\LeadsManagementModule\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class NewLeadCreatedNotification extends Notification implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $lead;

    public function __construct($lead)
    {
        $this->lead = $lead;
    }

    public function broadcastOn()
    {
        return new Channel('leads');
    }

    public function toArray($notifiable)
    {
        return [
            'lead_id' => $this->lead->id,
            'message' => 'A new lead is available.',
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
