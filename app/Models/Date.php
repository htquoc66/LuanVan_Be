<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_field',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'date_id');
    }
}
