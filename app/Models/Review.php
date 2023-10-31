<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'content', 'rating', 'status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
