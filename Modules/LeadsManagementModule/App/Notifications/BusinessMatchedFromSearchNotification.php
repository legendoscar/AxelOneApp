<?php

namespace Modules\LeadsManagementModule\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class BusinessMatchedFromSearchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $searchQuery;
    protected $searchFields;
    protected $business;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $searchQuery, $searchFields, $business)
    {
        $this->user = $user;
        $this->searchQuery = $searchQuery;
        $this->searchFields = $searchFields;
        $this->business = $business;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('New Lead Matched')
        ->greeting('Hello ' . $this->business->org_name . ',')
        ->line('A new lead has matched your business criteria.')
        ->line('Search Query: ' . $this->searchQuery)
        ->line('Search Fields: ' . implode(', ', $this->searchFields))
        ->line('Searched by: ' . $this->user->username . ' (' . $this->user->email . ')')
        ->action('View Details', url('/businesses/' . $this->business->id))
        ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'business_id' => $this->business->id,
            'search_query' => $this->searchQuery,
            'search_fields' => $this->searchFields,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
        ];
    }
}
