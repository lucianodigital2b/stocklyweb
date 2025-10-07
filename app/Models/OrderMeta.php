<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMeta extends Model
{
    /** @use HasFactory<\Database\Factories\OrderMetaFactory> */
    use HasFactory;

    protected $fillable = ['order_id', 'key', 'value'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
