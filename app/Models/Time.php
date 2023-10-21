<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_field',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'time_id');
    }
}
