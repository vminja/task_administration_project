<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build() {
        return $this->subject('Aktivacija naloga')
            ->view('emails.activate')
            ->with([
                'activationUrl' => url('/activate/' . $this->user->activation_token)
            ]);
    }
}
