<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnterpriseApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $enterprise;

    /**
     * Create a new message instance.
     */
    public function __construct($enterprise)
    {
        $this->enterprise = $enterprise;
    }

    /**
     * Build the message.
     */
    public function build()
    {
           return $this->from(get_static_option('site_global_email'), get_static_option('site_title'))->subject('Enterprise Registration Approved')
                    ->view('mail.enterprise_approved')
                    ->with(['enterprise' => $this->enterprise]);
    }
}
