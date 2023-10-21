<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotarizedDocument extends Model
{
    protected $table = 'user_notarized_document';

    protected $fillable = [
        'user_id',
        'notarized_document_id',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notarizedDocument()
    {
        return $this->belongsTo(NotarizedDocument::class, 'notarized_document_id');
    }
}
