<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostNotarizedDocument extends Model
{
    use HasFactory;

    protected $table = 'cost_notarized_document';

    protected $fillable = [
        'cost_id',
        'notarized_document_id',
        'description',
        // Các trường khác bạn cần thêm vào
    ];

    public function cost()
    {
        return $this->belongsTo(Cost::class, 'cost_id');
    }

    public function notarizedDocument()
    {
        return $this->belongsTo(NotarizedDocument::class, 'notarized_document_id');
    }
}
