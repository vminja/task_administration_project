<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ResetPasswordLinkMail extends Mailable
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
        return $this->subject('Resetovanje lozinke')
            ->view('emails.resetPassword')
            ->with([
                'resetLink' => url('/resetLink/' . $this->user->reset_token)
            ]);
    }
}
