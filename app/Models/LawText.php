<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawText extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file',
        'effective_date',
        'status',
        'category_id',

    ];

    public function notarizedDocuments()
    {
        return $this->belongsToMany(NotarizedDocument::class, 'law_text_notarized_document', 'law_text_id', 'notarized_document_id');
    }
}
