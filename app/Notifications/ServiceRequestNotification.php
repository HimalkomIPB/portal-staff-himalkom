<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ServiceRequest;

class ServiceRequestNotification extends Notification
{
    use Queueable;

    public $serviceRequest;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceRequest $serviceRequest, $message)
    {
        $this->serviceRequest = $serviceRequest;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'title' => 'Layanan: ' . $this->serviceRequest->title,
            'message' => $this->message,
            'url' => route('dashboard.services.show', $this->serviceRequest->id),
            'icon' => 'document-text',
        ];
    }
}
