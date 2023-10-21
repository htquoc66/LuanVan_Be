<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;
    protected $fillable = ['cost_type_id','name', 'price'];

    public function costType()
    {
        return $this->belongsTo(CostType::class, 'cost_type_id');
    }

    public function notarizedDocuments()
    {
        return $this->belongsToMany(NotarizedDocument::class, 'cost_notarized_document', 'cost_id', 'notarized_document_id')
            ->withPivot('description');
    }
}
