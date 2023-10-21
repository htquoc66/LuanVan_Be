<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'customer_id',
        'user_id',
        'cost',
        'payment_method',
        'file_pdf',
        'content',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notarizedDocuments()
    {
        return $this->hasMany(NotarizedDocument::class);
    }
}
