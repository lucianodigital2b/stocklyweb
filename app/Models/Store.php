<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;
use Spatie\MediaLibrary\InteractsWithMedia;

class Store extends Model
{
    
    use HasFactory, Billable, InteractsWithMedia;


    protected $fillable = ['name', 'domain'];

    public function storeUsers()
    {
        return $this->hasMany(StoreUser::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
}