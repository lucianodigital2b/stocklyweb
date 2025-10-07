<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingFee extends Model
{
    use HasFactory, SoftDeletes;   
    
    protected $fillable = [
        'zip_code_start',
        'zip_code_end',
        'polygon_name',
        'weight_start',
        'weight_end',
        'absolute_money_cost',
        'price_percent',
        'price_by_extra_weight',
        'max_volume',
        'time_cost',
        'country',
        'minimum_value_insurance',
        'time_cost_days',
    ];

    public function shipping_policy()
    {
        return $this->belongsTo(ShippingPolicy::class, 'shipping_policy_id');
    }

}
