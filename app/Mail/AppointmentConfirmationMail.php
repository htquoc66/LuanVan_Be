<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $appointmentDate;
    public $appointmentTime;
    public $appointmentContent;
    public $userName;

    public function __construct($email, $appointmentDate, $appointmentTime, $appointmentContent, $userName)
    {
        $this->email = $email;
        $this->appointmentDate = $appointmentDate;
        $this->appointmentTime = $appointmentTime;
        $this->appointmentContent = $appointmentContent;
        $this->userName = $userName;

    }

    public function build()
    {
        return $this->to($this->email)
            ->subject('Xác nhận cuộc hẹn')
            ->view('emails.appointment-confirmation')
            ->with([
                'appointmentDate' => $this->appointmentDate,
                'appointmentTime' => $this->appointmentTime,
                'appointmentContent' => $this->appointmentContent,
                'userName' => $this->userName,

            ]);
    }
}


