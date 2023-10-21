<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawTextNotarizedDocument extends Model
{
    use HasFactory;

    protected $table = 'law_text_notarized_document';

    public $timestamps = true;

    protected $fillable = [
        'law_text_id',
        'notarized_document_id',
    ];

    public function lawText()
    {
        return $this->belongsTo(LawText::class, 'law_text_id');
    }

    public function notarizedDocument()
    {
        return $this->belongsTo(NotarizedDocument::class, 'notarized_document_id');
    }
}
