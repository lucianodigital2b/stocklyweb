<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model
{
    use HasFactory, InteractsWithMedia;
    
    protected $fillable = ['store_id', 'name', 'description', 'price', 'sku'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function metas()
    {
        return $this->hasMany(ProductMeta::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}