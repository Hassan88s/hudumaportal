<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminLoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * $data example:
     * [
     *   'otp' => '123456',
     *   'minutes' => 5,
     *   'admin_name' => 'Super Admin' (optional)
     * ]
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->from(get_static_option('site_global_email'), get_static_option('site_' . get_default_language() . '_title'))
            ->subject(__('Your Admin Login OTP'))
            ->markdown('mail.admin_login_otp');
    }
}
