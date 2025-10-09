<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenant;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, MultiTenant;

    protected $fillable = [
        'store_id',
        'name',
        'status',
        'company_id'
    ];

    protected $casts = [
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Get the store that owns the category.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the company that owns the category.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the products associated with the category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive categories.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the products count for this category.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }
}
