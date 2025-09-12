<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GlobalAnnouncement extends Notification
{
    public function __construct(public string $title, public string $message) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Public' . ' - ' . $this->title,
            'message' => $this->message,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($notifiable->name)
            ->line($this->message)
            ->line('Thank you for your attention!');
    }
}
