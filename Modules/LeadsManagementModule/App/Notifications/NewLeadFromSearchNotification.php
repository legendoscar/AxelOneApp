<?php

namespace Modules\LeadsManagementModule\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;
use Illuminate\Notifications\Messages\BroadcastMessage;


class NewLeadFromSearchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $lead, $payload;

    public function __construct($lead, $payload)
    {
        $this->lead = $lead;
        $this->payload = $payload;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // Using both database and broadcast channels
    }

    public function toArray($notifiable)
    {

        $searchLead = $this->lead->load('searchRequest', 'user');
        return [
            'lead_id' => $this->lead->id,
            'message' => 'A new lead is available.',
            'search_request_data' => [
                'search term' => $searchLead->searchRequest->search_term,
                // 'other_field' => $searchLead->searchRequest->other_field,
            ],
            'user_data' => [
                'name' => $searchLead->user->lastname . ' ' . $searchLead->user->firstname,
                'email' => '**********', // Hide email
                'phone' => '**********', // Hide phone
            ]
        ];
    }

    // public function toBroadcast($notifiable)
    // {
    //     return new BroadcastMessage([
    //         'lead_id' => $this->lead->id,
    //         'message' => 'A new lead is available.',
    //         'searchTerm' => $this->lead->search_term,
    //         // 'searchFields' => $this->payload['searchFields'],
    //         'lead_partial_info' => $this->getPartialLeadInfo(),
    //     ]);
    // }

    // protected function getPartialLeadInfo()
    // {
    //     return [
    //         'name' => $searchLead->user->lastname . ' ' . $this->lead->firstname,
    //         'email' => '**********', // Hide email
    //         'phone' => '**********', // Hide phone
    //     ];
    // }
}
