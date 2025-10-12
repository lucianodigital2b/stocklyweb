<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'movement_type', 
        'user_id',
        'stock_before', 
        'stock_after',
        'is_infinite_before',
        'is_infinite_after',
        'inventory_id',
        'quantity_change',
    ];


    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
