<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public $unhashedPassword;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $unhashedPassword)
    {
        $this->data = $data;
        $this->unhashedPassword = $unhashedPassword;
    }

    public function build()
    {
        return $this->subject('Pemberitahuan Data Akun Himalkom')
            ->view('emails.user-created')
            ->with([
                'email' => $this->data['email'],
                'password' => $this->unhashedPassword,
            ]);
    }
}
