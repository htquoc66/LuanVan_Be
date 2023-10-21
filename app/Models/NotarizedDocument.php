<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotarizedDocument extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'file',
        'status',
        'date',
        'total_cost',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_notarized_document', 'notarized_document_id', 'customer_id')
            ->withPivot('description'); 
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notarized_document', 'notarized_document_id', 'user_id')
            ->withPivot('description'); 
    }

    public function lawTexts()
    {
        return $this->belongsToMany(LawText::class, 'law_text_notarized_document', 'notarized_document_id', 'law_text_id');
    }
    
    public function costs()
    {
        return $this->belongsToMany(Cost::class, 'cost_notarized_document', 'notarized_document_id', 'cost_id')
            ->withPivot('description');
    }
    public function storage()
    {
        return $this->hasOne(Storage::class, 'notarized_document_id');
    }

}
