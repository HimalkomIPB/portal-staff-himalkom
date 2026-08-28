<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Proposal;

class ProposalNotification extends Notification
{
    use Queueable;

    public $proposal;
    public $message;
    public $url;
    public $title;

    public function __construct(Proposal $proposal, $title, $message, $url)
    {
        $this->proposal = $proposal;
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => 'document-text',
        ];
    }
}
