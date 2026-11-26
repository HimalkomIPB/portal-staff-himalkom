<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkProgramCommentNotification extends Notification
{
    protected $title;

    protected $message;

    protected $url;

    public function __construct($title, $message, $url)
    {

        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.work-program-comment', [
                'title' => $this->title,
                'recipientName' => $notifiable->name ?? null,
                'commentMessage' => $this->message,
                'url' => $this->url,
            ]);
    }
}
