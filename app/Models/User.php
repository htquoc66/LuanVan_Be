<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'phone',
        'email',
        'gender',
        'date_of_birth',
        'password',
        'avatar'
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
        return $this->hasMany(Invoice::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class,'role_user', 'user_id', 'role_id');
    }


    public function notarizedDocuments()
    {
        return $this->belongsToMany(NotarizedDocument::class, 'user_notarized_document', 'user_id', 'notarized_document_id')
            ->withPivot('description'); 
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
