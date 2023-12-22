<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmationMail;

class SendAppointmentConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    public function handle()
    {
        Mail::to($this->email) // Địa chỉ người nhận
            ->send(new AppointmentConfirmationMail($this->email, $this->appointmentDate, $this->appointmentTime, $this->appointmentContent, $this->userName));
    }
    
}
