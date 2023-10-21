<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'type_id',
        'name',
        'idCard_number',
        'idCard_issued_date',
        'idCard_issued_place',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }
    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'type_id');
    }

    public function notarizedDocuments()
    {
        return $this->belongsToMany(NotarizedDocument::class, 'customer_notarized_document', 'customer_id', 'notarized_document_id')
            ->withPivot('description'); // Thêm pivot 'description' vào mối quan hệ
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }
}
