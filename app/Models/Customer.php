<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'store_id', 
        'name', 
        'email', 
        'phone',
        'document_number',
        'cep',
        'address',
        'number',
        'neighborhood',
        'city',
        'state',
        'birth_date',
        'customer_type',
        'newsletter_subscription',
        'status'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function metas()
    {
        return $this->hasMany(CustomerMeta::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
