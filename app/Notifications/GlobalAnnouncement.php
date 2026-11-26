<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
            'title' => 'Public'.' - '.$this->title,
            'message' => $this->message,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.global-announcement', [
                'title' => $this->title,
                'recipientName' => $notifiable->name ?? null,
                'announcementMessage' => $this->message,
            ]);
    }
}
