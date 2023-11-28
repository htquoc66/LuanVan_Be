<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    use HasFactory;
    protected $fillable = [
        'file',
        'zip_password',
        'notarized_document_id'
    ]; 

    public function notarizedDocument()
    {
        return $this->belongsTo(NotarizedDocument::class, 'notarized_document_id');
    }
}
