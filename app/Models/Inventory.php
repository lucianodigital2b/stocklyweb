<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'reorder_threshold',
        'reorder_quantity',
        'stock',
        'is_infinite'
    ];

    public function inventory_updates()
    {
        return $this->hasMany(StockMovement::class)->orderBy('id', 'desc');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getStockReservedAttribute()
    {
        return 0;
    }

    public function getStockShippingAttribute()
    {
        return 0;
    }

    public function getStockAvailableAttribute()
    {
        return $this->stock - $this->stock_shipping - $this->stock_reserved;
    }

    public static function getFieldBadge($status)
    {
        if($status) {
            return '<span class="badge bg-success">SIM</span>';
        }

        return '<span class="badge bg-secondary">NÃO</span>';
    }

}
