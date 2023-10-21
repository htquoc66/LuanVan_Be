<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\StaffMail;

class SendStaffMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $staffName;
    public $staffEmail;
    public $staffPassword;

    public function __construct($staffName, $staffEmail, $staffPassword)
    {
        $this->staffName = $staffName;
        $this->staffEmail = $staffEmail;
        $this->staffPassword = $staffPassword;
    }

    public function handle()
    {
        Mail::to($this->staffEmail)
            ->cc($this->staffEmail)
            ->bcc($this->staffEmail)
            ->send(new StaffMail($this->staffName, $this->staffEmail, $this->staffPassword));
    }
}