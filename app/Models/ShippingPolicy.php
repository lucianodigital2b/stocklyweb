<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingPolicy extends Model
{
    use HasFactory;

    public $table = 'shipping_policies';

    public $fillable = [
        'id_erp',
        'name',
        'shipping_method_id',
        'sum_dimensions',
        'biggest_edge',
        'cubic_weight_factor',
        'minimum_weight_factor',
        'delivery_saturdays',
        'delivery_sundays',
        'delivery_holidays', 
        'minimum_items',
        'minimum_value',
        'maximum_value',
        'relate_pick_up_points',
        'modal_undefined',
        'modal_chemicals',
        'modal_liquids',
        'modal_white_line',
        'modal_electronics',
        'modal_mattresses',
        'modal_fire_gun',
        'modal_furniture',
        'modal_refrigerators',
        'modal_glass',
        'modal_tires',
        'time_type',
        'monday_hour',
        'tuesday_hour',
        'wednesday_hour',
        'thursday_hour',
        'friday_hour',
        'saturday_hour',
        'sunday_hour',
        'collect_monday_start',
        'collect_monday_end',
        'collect_tuesday_start',
        'collect_tuesday_end',
        'collect_wednesday_start',
        'collect_wednesday_end',
        'collect_thursday_start',
        'collect_thursday_end',
        'collect_friday_start',
        'collect_friday_end',
        'collect_saturday_start',
        'collect_saturday_end',
        'collect_sunday_start',
        'collect_sunday_end'
    ];

    const TIME_TYPE_SHIPPING = "shipping";
    const TIME_TYPE_COLLECT = "collect";

    public static function getTimeTypes(){
        return [
            static::TIME_TYPE_SHIPPING => 'Periodo de envio',
            static::TIME_TYPE_COLLECT => 'Horário de coleta'
        ];
    }

    function shippingMethod()
    {
        return $this->hasOne(ShippingMethod::class,'id','shipping_method_id');
    }

    public function shipping_fees()
    {
        return $this->hasMany(ShippingFee::class);
    }

    public function pickups()
    {
        return $this->belongsToMany(Store::class);
    }
    
}
