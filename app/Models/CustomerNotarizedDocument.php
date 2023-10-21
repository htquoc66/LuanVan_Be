<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNotarizedDocument extends Model
{
    protected $table = 'customer_notarized_document';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'notarized_document_id',
        'description',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function notarizedDocument()
    {
        return $this->belongsTo(NotarizedDocument::class, 'notarized_document_id');
    }
}
