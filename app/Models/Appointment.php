<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'user_id',
        'date_id',
        'time_id',
        'content',
        'status',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function date()
    {
        return $this->belongsTo(Date::class, 'date_id');
    }

    public function time()
    {
        return $this->belongsTo(Time::class, 'time_id');
    }
}
