<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;

    protected $fillable = ['store_id', 'code', 'name', 'description', 'uses', 'max_uses', 'max_uses_user', 'discount_type', 'discount_amount', 'free_shipping', 'starts_at', 'expires_at'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
