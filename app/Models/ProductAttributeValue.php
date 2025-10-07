<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = ['attribute_id', 'product_variant_id', 'value'];

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}