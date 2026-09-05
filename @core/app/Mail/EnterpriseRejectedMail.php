<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnterpriseRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $enterprise;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct($enterprise, $rejectionReason)
    {
        $this->enterprise = $enterprise;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
       return $this->from(get_static_option('site_global_email'), get_static_option('site_title'))->subject('Enterprise Registration Rejected')
                    ->view('mail.enterprise_rejected')
                    ->with([
                        'enterprise' => $this->enterprise,
                        'rejectionReason' => $this->rejectionReason,
                    ]);
    }
}
