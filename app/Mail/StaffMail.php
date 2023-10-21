<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffMail extends Mailable
{
    use Queueable, SerializesModels;

    public $staffName;
    public $staffEmail;
    public $staffPassword;

    public function __construct($staffName, $staffEmail, $staffPassword)
    {
        $this->staffName = $staffName;
        $this->staffEmail = $staffEmail;
        $this->staffPassword = $staffPassword;
    }

    public function build()
    {
        return $this->to($this->staffEmail)
            ->subject('Mật khẩu đăng nhập công chứng CTU')
            ->view('emails.staff.welcome')
            ->with([
                'staffName' => $this->staffName,
                'staffEmail' => $this->staffEmail,
                'staffPassword' => $this->staffPassword,
            ]);
    }
}