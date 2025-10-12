<?php

namespace App\Models;

use App\Helpers\Formatting;
use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dock extends Model
{
    use HasFactory, MultiTenant;

    protected $fillable = [
        'name',
        'service_time_days',
        'service_time_seconds',
        'overhead_days',
        'overhead_seconds',
        'postal_code',
        'address',
        'address_number',
        'address_complement',
        'neighborhood',
        'city',
        'state',
        'country',
        'status',
    ];


    public function shipping_policies()
    {
        return $this->belongsToMany(ShippingPolicy::class, 'docks_shipping_policies', 'dock_id');
    }

    public function store_fronts()
    {
        return $this->belongsToMany(StoreFront::class, 'docks_store_fronts', 'dock_id');
    }

    public function getServiceTimeHoursAttribute()
    {
        return Formatting::secondsToInputTime($this->service_time_seconds);
    }

    public function getOverheadHoursAttribute()
    {
        return Formatting::secondsToInputTime($this->overhead_seconds);
    }
    
    public function getPoliticasStrAttribute()
    {
        $countPolitica = $this->shipping_policies->count();
        if(!$countPolitica)
        {
            return null;
        }
        return $countPolitica. ($countPolitica == 1 ? ' Política': ' Políticas' ). ' de envio';
    }

    public function warehouses()
    {
        return $this->belongsToMany(Dock::class, 'warehouse_docks', 'dock_id');
    }
    
}
